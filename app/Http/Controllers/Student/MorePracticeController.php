<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MorePracticeController extends Controller
{
    public const CATEGORIES = [
        'grammar' => ['icon' => 'pencil', 'title' => 'Grammar Practice', 'description' => 'Extra exercises on the grammar you have already studied.'],
        'listening' => ['icon' => 'music-note', 'title' => 'Listening Practice', 'description' => 'Short recordings with questions. Play them as often as you like.'],
        'reading' => ['icon' => 'book-open', 'title' => 'Reading Practice', 'description' => 'Notices, letters and messages with comprehension questions.'],
        'speaking' => ['icon' => 'microphone', 'title' => 'Speaking Practice', 'description' => 'Situations to respond to, with practice questions.'],
    ];

    public function index(): View
    {
        $track = Auth::user()->studentProfile?->track;

        return view('student.more-practice', [
            'levelLabel' => $track?->track_code,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function show(string $category): View
    {
        abort_unless(array_key_exists($category, self::CATEGORIES), 404);

        $track = Auth::user()->studentProfile?->track;

        return view('student.more-practice-session', [
            'category' => $category,
            'meta' => self::CATEGORIES[$category],
            'levelLabel' => $track?->track_code,
        ]);
    }
}
