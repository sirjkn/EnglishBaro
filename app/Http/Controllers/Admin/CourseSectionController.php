<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseSectionController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section = $course->sections()->create($validated + [
            'order' => $course->sections()->max('order') + 1,
        ]);

        AuditLogger::log('admin.course_section.created', $section, [], $validated);

        return back()->with('status', 'Section added.');
    }

    public function update(Request $request, Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($section->course_id === $course->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $section->update($validated);

        return back()->with('status', 'Section updated.');
    }

    public function destroy(Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($section->course_id === $course->id, 404);

        AuditLogger::log('admin.course_section.deleted', $section, $section->toArray());

        $section->delete();

        return back()->with('status', 'Section deleted.');
    }
}
