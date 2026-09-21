<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\CourseProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LearningController extends Controller
{
    public function show(Course $course): RedirectResponse
    {
        $this->authorize('learn', $course);

        $enrollment = $this->enrollmentFor($course);

        $lesson = $enrollment->last_viewed_lesson_id
            ? Lesson::find($enrollment->last_viewed_lesson_id)
            : $course->lessons()->orderBy('order')->first();

        $lesson ??= $course->lessons()->orderBy('order')->first();

        abort_if(! $lesson, 404);

        return redirect()->route('student.courses.learn', [$course, $lesson]);
    }

    public function lesson(Course $course, Lesson $lesson): View
    {
        $this->authorize('learn', $course);
        $this->authorize('view', $lesson);

        abort_unless($lesson->course_id === $course->id, 404);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($course);

        $lesson->load(['video', 'ebook.file', 'resources.media', 'section']);

        $sections = $course->sections()->with(['lessons' => function ($query) {
            $query->orderBy('order');
        }])->orderBy('order')->get();

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        $allLessons = $sections->flatMap->lessons;
        $currentIndex = $allLessons->search(fn ($item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        $progress = LessonProgress::query()->firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['enrollment_id' => $enrollment->id, 'status' => 'started', 'started_at' => now()]
        );

        $progress->update(['last_viewed_at' => now()]);
        $enrollment->update(['last_viewed_lesson_id' => $lesson->id, 'last_viewed_at' => now()]);

        return view('student.learn', [
            'course' => $course,
            'lesson' => $lesson,
            'sections' => $sections,
            'completedLessonIds' => $completedLessonIds,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'isCompleted' => $progress->status === 'completed',
        ]);
    }

    public function complete(Course $course, Lesson $lesson, CourseProgressService $courseProgressService): RedirectResponse
    {
        $this->authorize('learn', $course);
        $this->authorize('view', $lesson);

        abort_unless($lesson->course_id === $course->id, 404);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($course);

        LessonProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'enrollment_id' => $enrollment->id,
                'status' => 'completed',
                'completed_at' => now(),
                'last_viewed_at' => now(),
            ]
        );

        $courseProgressService->recalculate($user, $course, $enrollment);

        $sections = $course->sections()->with(['lessons' => fn ($q) => $q->orderBy('order')])->orderBy('order')->get();
        $allLessons = $sections->flatMap->lessons;
        $currentIndex = $allLessons->search(fn ($item) => $item->id === $lesson->id);
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        if ($nextLesson) {
            return redirect()->route('student.courses.learn', [$course, $nextLesson])
                ->with('status', 'Lesson marked as completed!');
        }

        return redirect()->route('student.courses.learn', [$course, $lesson])
            ->with('status', 'Course completed! Your certificate has been issued.');
    }

    public function updateVideoProgress(Request $request, Course $course, Lesson $lesson): \Illuminate\Http\JsonResponse
    {
        $this->authorize('learn', $course);
        $this->authorize('view', $lesson);

        $validated = $request->validate([
            'seconds' => ['required', 'integer', 'min:0'],
        ]);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($course);

        $progress = LessonProgress::query()->firstOrNew(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
        );

        $progress->enrollment_id = $enrollment->id;
        $progress->video_progress_seconds = $validated['seconds'];
        $progress->last_viewed_at = now();

        if (! in_array($progress->status, ['started', 'completed'], true)) {
            $progress->status = 'started';
            $progress->started_at ??= now();
        }

        $progress->save();

        return response()->json(['status' => 'ok']);
    }

    private function enrollmentFor(Course $course)
    {
        return Auth::user()->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->firstOrFail();
    }
}
