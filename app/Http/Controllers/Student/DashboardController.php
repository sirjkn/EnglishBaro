<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $enrollments = $user->enrollments()
            ->where('status', 'active')
            ->with(['course.level', 'progress', 'subscription'])
            ->latest('enrolled_at')
            ->get();

        $upcomingPayment = $enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter()
            ->filter(fn ($subscription) => $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $recentNotifications = AppNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', [
            'enrollments' => $enrollments,
            'upcomingPayment' => $upcomingPayment,
            'recentNotifications' => $recentNotifications,
            'studentProfile' => $user->studentProfile,
        ]);
    }
}
