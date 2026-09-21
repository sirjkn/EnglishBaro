<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyCourseController extends Controller
{
    public function index(): View
    {
        $enrollments = Auth::user()->enrollments()
            ->with(['course.level', 'course.thumbnail', 'progress', 'subscription'])
            ->latest('enrolled_at')
            ->get();

        return view('student.courses.index', [
            'enrollments' => $enrollments,
        ]);
    }
}
