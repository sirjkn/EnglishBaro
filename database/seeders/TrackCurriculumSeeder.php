<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackCurriculumSeeder extends Seeder
{
    /**
     * Every track has 100 levels, and every level has the same four sections.
     */
    private const LEVELS_PER_TRACK = 100;

    private const LESSONS_PER_SECTION = 2;

    public function run(): void
    {
        $now = now();

        foreach (Track::query()->orderBy('order')->get() as $track) {
            if ($track->levels()->count() >= self::LEVELS_PER_TRACK) {
                continue;
            }

            $existingNumbers = $track->levels()->pluck('number')->all();

            $levelRows = [];

            foreach (range(1, self::LEVELS_PER_TRACK) as $number) {
                if (in_array($number, $existingNumbers, true)) {
                    continue;
                }

                $levelRows[] = [
                    'track_id' => $track->id,
                    'number' => $number,
                    'title' => "Level {$number}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($levelRows, 200) as $chunk) {
                DB::table('levels')->insert($chunk);
            }

            $levelIds = $track->levels()->pluck('id', 'number');

            $sectionRows = [];

            foreach ($levelIds as $number => $levelId) {
                foreach (array_values(Section::TYPES) as $order => $type) {
                    $sectionRows[] = [
                        'level_id' => $levelId,
                        'type' => $type,
                        'title' => Section::titleFor($type),
                        'description' => Section::titleFor($type)." practice for {$track->track_code} level {$number}.",
                        'order' => $order,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($sectionRows, 500) as $chunk) {
                DB::table('sections')->insert($chunk);
            }

            $sections = DB::table('sections')
                ->join('levels', 'levels.id', '=', 'sections.level_id')
                ->where('levels.track_id', $track->id)
                ->get(['sections.id', 'sections.type', 'levels.number']);

            $lessonRows = [];

            foreach ($sections as $section) {
                foreach (range(1, self::LESSONS_PER_SECTION) as $index) {
                    $lessonRows[] = [
                        'section_id' => $section->id,
                        'title' => Section::titleFor($section->type)." {$section->number}.{$index}",
                        'description' => 'Lesson content coming soon.',
                        'order' => $index - 1,
                        'duration_seconds' => 300,
                        'is_preview' => $section->number === 1 && $index === 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($lessonRows, 500) as $chunk) {
                DB::table('lessons')->insert($chunk);
            }

            $this->seedGrammarActivities($track, $now);
        }
    }

    /**
     * Every Grammar lesson gets one interactive check-your-answers activity
     * so the "Check Answers" / gated "Next Lesson" flow has real content.
     */
    private function seedGrammarActivities(Track $track, $now): void
    {
        $grammarLessons = DB::table('lessons')
            ->join('sections', 'sections.id', '=', 'lessons.section_id')
            ->join('levels', 'levels.id', '=', 'sections.level_id')
            ->where('levels.track_id', $track->id)
            ->where('sections.type', 'grammar')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('assessments')
                    ->whereColumn('assessments.lesson_id', 'lessons.id');
            })
            ->get(['lessons.id as lesson_id', 'levels.number as level_number']);

        foreach ($grammarLessons as $lesson) {
            $assessmentId = DB::table('assessments')->insertGetId([
                'level_id' => DB::table('levels')->where('track_id', $track->id)->where('number', $lesson->level_number)->value('id'),
                'lesson_id' => $lesson->lesson_id,
                'title' => 'Grammar Check',
                'description' => 'Answer the questions below, then press Check Answers.',
                'type' => 'quiz',
                'passing_score' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $questionId = DB::table('assessment_questions')->insertGetId([
                'assessment_id' => $assessmentId,
                'question' => 'Choose the correct form: She ___ to school every day.',
                'type' => 'multiple_choice',
                'points' => 1,
                'order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('assessment_options')->insert([
                ['assessment_question_id' => $questionId, 'option_text' => 'go', 'is_correct' => false, 'order' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['assessment_question_id' => $questionId, 'option_text' => 'goes', 'is_correct' => true, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['assessment_question_id' => $questionId, 'option_text' => 'going', 'is_correct' => false, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('assessment_questions')->insert([
                'assessment_id' => $assessmentId,
                'question' => 'Fill in the blank: I have ___ apple.',
                'type' => 'short_answer',
                'correct_short_answer' => 'an',
                'points' => 1,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
