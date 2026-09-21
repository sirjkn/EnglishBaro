<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __invoke(): View
    {
        return view('student.progress');
    }
}
