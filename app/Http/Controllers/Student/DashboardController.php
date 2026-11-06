<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\StudentDashboardSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(StudentDashboardSummaryService $summary): View
    {
        $user = Auth::user();

        $recentNotifications = AppNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', $summary->forUser($user) + [
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
