<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // A student who has already started a course is dropped back into
        // the exact lesson they left off on, instead of the home page —
        // their most recently viewed enrollment decides which one.
        $enrollment = $user->enrollments()
            ->whereNotNull('last_viewed_lesson_id')
            ->where('status', 'active')
            ->with(['lastViewedLesson.section.level', 'track'])
            ->orderByDesc('last_viewed_at')
            ->first();

        $lesson = $enrollment?->lastViewedLesson;
        $level = $lesson?->section?->level;

        if ($lesson && $level && $enrollment->track) {
            return redirect()->route('student.tracks.learn', [$enrollment->track, $level, $lesson]);
        }

        return redirect()->route('home');
    }
}
