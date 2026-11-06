<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Track;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Track::class);

        // Grouped by course, same as Units (Tracks) and Courses — enrollments
        // for a track in one course are otherwise indistinguishable from a
        // same-coded track in another (both can be "A1", say).
        $courses = Course::query()
            ->with(['tracks' => function ($query) {
                $query->withCount('enrollments')
                    ->with(['enrollments' => fn ($q) => $q->with(['user', 'subscription', 'progress'])->latest('enrolled_at')])
                    ->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        // A student is "enrolled in the course" once they hold any active
        // enrollment in one of its tracks — one payment buys every track at
        // once, so this is really a per-course headcount, not a per-track one.
        $courseEnrollmentCounts = Enrollment::query()
            ->join('tracks', 'tracks.id', '=', 'enrollments.track_id')
            ->selectRaw('tracks.course_id, count(distinct enrollments.user_id) as total')
            ->groupBy('tracks.course_id')
            ->pluck('total', 'course_id');

        $overview = [
            'total' => Enrollment::query()->count(),
            'active' => Enrollment::query()->where('status', 'active')->count(),
            'cancelled' => Enrollment::query()->where('status', 'cancelled')->count(),
        ];

        return view('admin.enrollments.index', [
            'courses' => $courses,
            'courseEnrollmentCounts' => $courseEnrollmentCounts,
            'overview' => $overview,
        ]);
    }

    /**
     * Cancels the student's access without deleting payment history — the
     * track stays prepaid/recorded, but the enrollment and subscription no
     * longer grant learning access.
     */
    public function cancel(Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        $previous = $enrollment->only(['status']);

        $enrollment->update(['status' => 'cancelled']);
        $enrollment->subscription?->update(['status' => 'cancelled']);

        AuditLogger::log('admin.enrollment.cancelled', $enrollment, $previous, ['status' => 'cancelled']);

        return back()->with('status', 'Enrollment cancelled.');
    }
}
