<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Track;
use Illuminate\Support\Facades\DB;

/**
 * Builds out a track's full curriculum: 100 levels, each with the four
 * fixed sections (Grammar/Listening/Speaking/Reading), sample lessons, and
 * one Activity Questions assessment per section. Used both by the initial
 * database seeder and by admins creating a new track from the UI.
 */
class TrackCurriculumGenerator
{
    private const LEVELS_PER_TRACK = 100;

    private const LESSONS_PER_SECTION = 2;

    /**
     * Sample Activity Questions per section type, shown after that section's
     * lessons. One multiple-choice + one short-answer question each.
     */
    private const ACTIVITY_QUESTIONS = [
        'grammar' => [
            ['question' => 'Choose the correct form: She ___ to school every day.', 'type' => 'multiple_choice', 'options' => ['go', 'goes', 'going'], 'correct' => 'goes'],
            ['question' => 'Fill in the blank: I have ___ apple.', 'type' => 'short_answer', 'answer' => 'an'],
        ],
        'listening' => [
            ['question' => 'In the audio, what does the speaker say the weather will be like?', 'type' => 'multiple_choice', 'options' => ['Sunny', 'Rainy', 'Snowy'], 'correct' => 'Rainy'],
            ['question' => 'Write the phone number you heard in the recording.', 'type' => 'short_answer', 'answer' => '555-0142'],
        ],
        'speaking' => [
            ['question' => 'Which phrase is the most polite way to greet a stranger?', 'type' => 'multiple_choice', 'options' => ['Hey you', 'Good morning', 'What do you want'], 'correct' => 'Good morning'],
            ['question' => 'Type one greeting you would use when meeting someone for the first time.', 'type' => 'short_answer', 'answer' => 'Nice to meet you'],
        ],
        'reading' => [
            ['question' => 'According to the passage, where did the story take place?', 'type' => 'multiple_choice', 'options' => ['A city', 'A village', 'A forest'], 'correct' => 'A village'],
            ['question' => 'Write one word that describes the main character.', 'type' => 'short_answer', 'answer' => 'brave'],
        ],
    ];

    public function generate(Track $track): void
    {
        $now = now();

        if ($track->levels()->count() >= self::LEVELS_PER_TRACK) {
            return;
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

        $this->seedSectionActivities($track, $now);
    }

    private function seedSectionActivities(Track $track, $now): void
    {
        $sections = DB::table('sections')
            ->join('levels', 'levels.id', '=', 'sections.level_id')
            ->where('levels.track_id', $track->id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('assessments')
                    ->whereColumn('assessments.section_id', 'sections.id');
            })
            ->get(['sections.id', 'sections.type', 'sections.level_id']);

        foreach ($sections as $section) {
            $assessmentId = DB::table('assessments')->insertGetId([
                'level_id' => $section->level_id,
                'section_id' => $section->id,
                'title' => Section::titleFor($section->type).' Activity Questions',
                'description' => 'Answer the questions below, then press Check Answers.',
                'type' => 'quiz',
                'passing_score' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (self::ACTIVITY_QUESTIONS[$section->type] as $order => $data) {
                $questionId = DB::table('assessment_questions')->insertGetId([
                    'assessment_id' => $assessmentId,
                    'question' => $data['question'],
                    'type' => $data['type'],
                    'correct_short_answer' => $data['type'] === 'short_answer' ? $data['answer'] : null,
                    'points' => 1,
                    'order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($data['type'] === 'multiple_choice') {
                    $optionRows = collect($data['options'])->values()->map(fn ($text, $index) => [
                        'assessment_question_id' => $questionId,
                        'option_text' => $text,
                        'is_correct' => $text === $data['correct'],
                        'order' => $index,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('assessment_options')->insert($optionRows);
                }
            }
        }
    }
}
