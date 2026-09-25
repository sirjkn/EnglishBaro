<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Section;
use Illuminate\Support\Collection;

/**
 * Builds the ordered list of steps a student walks through inside a level:
 * each section's lessons, followed by that section's Activity Questions (if
 * it has one), then the next section's lessons, and so on. Both the lesson
 * page and the activity page use this to find their previous/next step.
 */
class LevelSequenceService
{
    public function nodes(Level $level): Collection
    {
        $sections = $level->sections()
            ->with([
                'lessons' => fn ($query) => $query->orderBy('order'),
                'activity',
            ])
            ->get();

        $nodes = collect();

        foreach ($sections as $section) {
            foreach ($section->lessons as $lesson) {
                $nodes->push((object) ['type' => 'lesson', 'lesson' => $lesson, 'section' => $section]);
            }

            if ($section->activity) {
                $nodes->push((object) ['type' => 'activity', 'section' => $section, 'assessment' => $section->activity]);
            }
        }

        return $nodes;
    }

    public function neighboursOfLesson(Collection $nodes, Lesson $lesson): array
    {
        $index = $nodes->search(fn ($node) => $node->type === 'lesson' && $node->lesson->id === $lesson->id);

        return $this->neighboursAt($nodes, $index);
    }

    public function neighboursOfActivity(Collection $nodes, Section $section): array
    {
        $index = $nodes->search(fn ($node) => $node->type === 'activity' && $node->section->id === $section->id);

        return $this->neighboursAt($nodes, $index);
    }

    /**
     * @return array{0: ?object, 1: ?object}
     */
    private function neighboursAt(Collection $nodes, int|false $index): array
    {
        if ($index === false) {
            return [null, null];
        }

        return [
            $index > 0 ? $nodes[$index - 1] : null,
            $index < $nodes->count() - 1 ? $nodes[$index + 1] : null,
        ];
    }

    public function activityFor(Section $section): ?Assessment
    {
        return Assessment::query()
            ->where('section_id', $section->id)
            ->where('is_active', true)
            ->first();
    }
}
