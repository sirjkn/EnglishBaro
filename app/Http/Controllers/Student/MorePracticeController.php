<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MorePracticeController extends Controller
{
    public function __invoke(): View
    {
        $track = Auth::user()->studentProfile?->track;

        return view('student.more-practice', [
            'levelLabel' => $track?->track_code,
        ]);
    }
}
