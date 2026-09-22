<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->where('user_type', 'student')->with(['studentProfile.level']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile', fn ($sq) => $sq->where('student_id', 'like', "%{$search}%"));
            });
        }

        if ($levelId = $request->integer('level')) {
            $query->whereHas('studentProfile', fn ($q) => $q->where('level_id', $levelId));
        }

        if ($country = $request->string('country')->value()) {
            $query->whereHas('studentProfile', fn ($q) => $q->where('country', $country));
        }

        if ($status = $request->string('status')->value()) {
            $query->where('is_active', $status === 'active');
        }

        $students = $query->withCount('enrollments')->latest()->paginate(15)->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'levels' => Level::query()->orderBy('order')->get(),
            'overview' => [
                'total' => User::query()->where('user_type', 'student')->count(),
                'active' => User::query()->where('user_type', 'student')->where('is_active', true)->count(),
                'new_this_month' => User::query()->where('user_type', 'student')->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'filters' => $request->only(['search', 'level', 'country', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.students.create', ['countries' => Countries::all()]);
    }

    public function store(Request $request, RegisterUserAction $registerUserAction): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = $registerUserAction->execute($validated);

        AuditLogger::log('admin.student.created', $user, [], ['email' => $user->email]);

        return redirect()->route('admin.students.show', $user)->with('status', 'Student created.');
    }

    public function show(User $student): View
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('view', $student);

        $student->load(['studentProfile.level', 'enrollments.course', 'enrollments.subscription', 'userSessions', 'payments.course']);

        $sessionQuota = app(\App\Services\SessionQuotaService::class);

        return view('admin.students.show', [
            'student' => $student,
            'sessionsThisMonth' => $sessionQuota->countThisMonth($student),
            'sessionMonthlyLimit' => \App\Services\SessionQuotaService::MONTHLY_LIMIT,
        ]);
    }

    public function resetSessionQuota(User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        $cleared = app(\App\Services\SessionQuotaService::class)->resetForCurrentMonth($student);

        AuditLogger::log('admin.student.session_quota_reset', $student, [], ['cleared' => $cleared]);

        return back()->with('status', "Session quota reset. {$cleared} inactive session(s) cleared for this month.");
    }

    public function edit(User $student): View
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        $student->load('studentProfile');

        return view('admin.students.edit', [
            'student' => $student,
            'levels' => Level::query()->orderBy('order')->get(),
            'countries' => Countries::all(),
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->user_type === 'student', 404);
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'is_active' => ['boolean'],
        ]);

        $student->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $student->studentProfile()->update([
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'level_id' => $validated['level_id'] ?? null,
        ]);

        AuditLogger::log('admin.student.updated', $student, [], $validated);

        return redirect()->route('admin.students.show', $student)->with('status', 'Student updated.');
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
