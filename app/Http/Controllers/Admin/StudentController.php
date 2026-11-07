<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        // Each student's inline panel (Track/Active Sessions/Payments) needs
        // these relations loaded too, since it's rendered right here on the
        // list rather than on a separate profile page.
        $query = User::query()->where('user_type', 'student')->with([
            'studentProfile.track',
            'studentProfile.profileImage',
            'enrollments.track.course.tracks',
            'enrollments.subscription',
            'userSessions',
            'payments.track',
        ]);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile', fn ($sq) => $sq->where('student_id', 'like', "%{$search}%"));
            });
        }

        if ($trackId = $request->integer('track')) {
            $query->whereHas('studentProfile', fn ($q) => $q->where('track_id', $trackId));
        }

        if ($country = $request->string('country')->value()) {
            $query->whereHas('studentProfile', fn ($q) => $q->where('country', $country));
        }

        if ($status = $request->string('status')->value()) {
            $query->where('is_active', $status === 'active');
        }

        // Oldest first, so a newly created student appears at the bottom of
        // the list rather than bumping everyone else down.
        $students = $query->withCount('enrollments')->oldest()->paginate(15)->withQueryString();

        // Edit forms for every student on this page are rendered inline in a
        // hidden panel each (toggled by Alpine) rather than on a separate
        // page per student, so their studentProfile is already eager-loaded
        // above and this just decides which one (if any) opens by default.
        $openStudentId = $request->integer('open') ?: $students->first()?->id;

        $sessionQuota = app(\App\Services\SessionQuotaService::class);
        $sessionsThisMonthByStudentId = collect($students->items())
            ->mapWithKeys(fn ($student) => [$student->id => $sessionQuota->countThisMonth($student)]);

        return view('admin.students.index', [
            'students' => $students,
            'tracks' => Track::query()->orderBy('order')->get(),
            'countries' => Countries::all(),
            'openStudentId' => $openStudentId,
            'sessionsThisMonthByStudentId' => $sessionsThisMonthByStudentId,
            'sessionMonthlyLimit' => \App\Services\SessionQuotaService::MONTHLY_LIMIT,
            'overview' => [
                'total' => User::query()->where('user_type', 'student')->count(),
                'active' => User::query()->where('user_type', 'student')->where('is_active', true)->count(),
                'new_this_month' => User::query()->where('user_type', 'student')->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'filters' => $request->only(['search', 'track', 'country', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.students.create', ['countries' => Countries::all()]);
    }

    public function store(Request $request, RegisterUserAction $registerUserAction, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->whereNull('deleted_at')],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);

        $user = $registerUserAction->execute($validated);

        if ($request->hasFile('avatar')) {
            $user->studentProfile()->update([
                'profile_image_id' => $mediaService->storeUploadedFile($request->file('avatar'), 'image')->id,
            ]);
        }

        AuditLogger::log('admin.student.created', $user, [], ['email' => $user->email]);

        // Back to the list with the new student open in the inline panel,
        // instead of navigating to a separate full-page profile.
        return redirect()->route('admin.students.index', ['open' => $user->id])->with('status', 'Student created.');
    }

    public function show(User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('view', $student);

        // The standalone profile page was retired in favor of the inline
        // edit panel on the students list — links elsewhere in the admin
        // still point here, so keep this route alive as a redirect.
        return redirect()->route('admin.students.index', ['open' => $student->id]);
    }

    public function resetSessionQuota(User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        $cleared = app(\App\Services\SessionQuotaService::class)->resetForCurrentMonth($student);

        AuditLogger::log('admin.student.session_quota_reset', $student, [], ['cleared' => $cleared]);

        return back()->with('status', "Session quota reset. {$cleared} inactive session(s) cleared for this month.");
    }

    /**
     * Generates a new random password for the student and shows it once to
     * the admin (flash session only — never logged or stored in plain text)
     * so it can be shared with the student out of band.
     */
    public function resetPassword(User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        $newPassword = Str::password(12);

        $student->update(['password' => Hash::make($newPassword)]);

        AuditLogger::log('admin.student.password_reset', $student, [], []);

        return back()->with('status', 'Password reset.')->with('newPassword', $newPassword);
    }

    /**
     * Manually marks the student's email as verified — for when the
     * verification email never arrives or its link doesn't work, so they
     * aren't blocked from logging in by the 'verified' middleware.
     */
    public function verifyEmail(User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        if (! $student->hasVerifiedEmail()) {
            $student->markEmailAsVerified();

            AuditLogger::log('admin.student.email_verified', $student, [], ['email_verified_at' => $student->email_verified_at]);
        }

        return back()->with('status', 'Email marked as verified.');
    }

    public function update(Request $request, User $student, MediaService $mediaService): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($student->id)->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female'],
            'referral_email' => ['nullable', 'string', 'email', 'max:255'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);

        $emailChanged = $validated['email'] !== $student->email;

        $student->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
            'email_verified_at' => $emailChanged ? null : $student->email_verified_at,
        ]);

        $profileUpdates = [
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'referral_email' => $validated['referral_email'] ?? null,
            'track_id' => $validated['track_id'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $profileUpdates['profile_image_id'] = $mediaService->storeUploadedFile($request->file('avatar'), 'image')->id;
        }

        $student->studentProfile()->update($profileUpdates);

        AuditLogger::log('admin.student.updated', $student, [], $validated);

        $redirectTo = \App\Support\SafeRedirect::resolve(
            $request->input('redirect'),
            route('admin.students.show', $student)
        );

        return redirect($redirectTo)->with('status', 'Student updated.');
    }

    public function destroy(User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('delete', $student);

        AuditLogger::log('admin.student.deleted', $student, ['email' => $student->email]);

        $student->delete();

        return redirect()->route('admin.students.index')->with('status', 'Student removed.');
    }
}
