<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $tracks = Track::query()
            ->withCount('enrollments')
            ->with(['enrollments' => function ($query) {
                $query->with(['user', 'subscription', 'progress'])->latest('enrolled_at');
            }])
            ->orderBy('order')
            ->get();

        return view('admin.enrollments.index', [
            'tracks' => $tracks,
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
