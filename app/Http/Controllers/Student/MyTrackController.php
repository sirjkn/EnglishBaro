<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LessonProgress;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyTrackController extends Controller
{
    public function index(): View
    {
        $enrollments = Auth::user()->enrollments()
            ->with(['track.thumbnail', 'progress', 'subscription'])
            ->latest('enrolled_at')
            ->get();

        return view('student.tracks.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function show(Track $track): View
    {
        $this->authorize('learn', $track);

        $enrollment = Auth::user()->enrollments()
            ->where('track_id', $track->id)
            ->where('status', 'active')
            ->first();

        $levels = $track->levels()->with('sections.lessons:id,section_id')->paginate(20);

        $completedLessonIds = $enrollment
            ? LessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'completed')
                ->pluck('lesson_id')
            : collect();

        return view('student.tracks.show', [
            'track' => $track,
            'levels' => $levels,
            'enrollment' => $enrollment,
            'completedLessonIds' => $completedLessonIds,
        ]);
    }
}
