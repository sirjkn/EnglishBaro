<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentDashboardSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(StudentDashboardSummaryService $summary): View
    {
        $user = Auth::user();

        return view('student.dashboard', $summary->forUser($user));
    }
}
