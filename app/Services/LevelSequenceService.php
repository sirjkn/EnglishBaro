<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\Level;
use Illuminate\Support\Collection;

/**
 * Builds the ordered list of steps a student walks through inside a level:
 * each lesson, followed immediately by that lesson's Activity Questions (if
 * it has one), then the next lesson, and so on. Both the lesson page and the
 * activity page use this to find their previous/next step.
 */
class LevelSequenceService
{
    public function nodes(Level $level): Collection
    {
        $sections = $level->sections()
            ->with([
                'lessons' => fn ($query) => $query->orderBy('order')->with('activity'),
            ])
            ->get();

        $nodes = collect();

        foreach ($sections as $section) {
            foreach ($section->lessons as $lesson) {
                $nodes->push((object) ['type' => 'lesson', 'lesson' => $lesson, 'section' => $section]);

                if ($lesson->activity) {
                    $nodes->push((object) ['type' => 'activity', 'lesson' => $lesson, 'section' => $section, 'assessment' => $lesson->activity]);
                }
            }
        }

        return $nodes;
    }

    public function neighboursOfLesson(Collection $nodes, Lesson $lesson): array
    {
        $index = $nodes->search(fn ($node) => $node->type === 'lesson' && $node->lesson->id === $lesson->id);

        return $this->neighboursAt($nodes, $index);
    }

    public function neighboursOfActivity(Collection $nodes, Lesson $lesson): array
    {
        $index = $nodes->search(fn ($node) => $node->type === 'activity' && $node->lesson->id === $lesson->id);

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

    public function activityFor(Lesson $lesson): ?Assessment
    {
        return Assessment::query()
            ->where('lesson_id', $lesson->id)
            ->where('is_active', true)
            ->first();
    }
}
