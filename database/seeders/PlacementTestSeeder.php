<?php

namespace Database\Seeders;

use App\Models\PlacementQuestion;
use App\Models\Track;
use Illuminate\Database\Seeder;

class PlacementTestSeeder extends Seeder
{
    /**
     * Each question offers one answer per track (A1..B2), roughly ordered
     * from the simplest correct usage to the most advanced. Whichever track's
     * answers the student picks most often becomes their placement.
     */
    private const QUESTIONS = [
        [
            'question' => 'Choose the correct sentence.',
            'options' => [
                'A1' => 'She go to school every day.',
                'A2' => 'She goes to school every day.',
                'B1' => 'She has been going to school every day.',
                'B2' => 'She would have gone to school every day, had it not rained.',
            ],
        ],
        [
            'question' => 'Which sentence uses the correct verb tense?',
            'options' => [
                'A1' => 'I am live in London.',
                'A2' => 'I live in London since 2020.',
                'B1' => "I've lived in London since 2020.",
                'B2' => 'Had I not moved, I would still be living in London.',
            ],
        ],
        [
            'question' => 'Pick the best way to ask for something politely.',
            'options' => [
                'A1' => 'Give me water.',
                'A2' => 'Can I have some water, please?',
                'B1' => 'Would you mind bringing me some water?',
                'B2' => 'I would be most grateful if you could bring me some water.',
            ],
        ],
        [
            'question' => 'Choose the sentence with the correct conditional form.',
            'options' => [
                'A1' => 'If it rain, I stay home.',
                'A2' => "If it rains, I'll stay home.",
                'B1' => 'If it rained, I would stay home.',
                'B2' => 'If it had rained, I would have stayed home.',
            ],
        ],
        [
            'question' => 'Which sentence best describes a past habit?',
            'options' => [
                'A1' => 'I play football when I young.',
                'A2' => 'I played football when I was young.',
                'B1' => 'I used to play football when I was young.',
                'B2' => 'I would often play football back when I was younger, though I rarely do now.',
            ],
        ],
        [
            'question' => 'Choose the sentence with correct use of the passive voice.',
            'options' => [
                'A1' => 'The cake made by her.',
                'A2' => 'The cake was made by her.',
                'B1' => 'The cake has been made by her already.',
                'B2' => 'Had the cake not been made by her, we would have ordered one.',
            ],
        ],
    ];

    public function run(): void
    {
        if (PlacementQuestion::query()->exists()) {
            return;
        }

        $tracks = Track::query()->pluck('id', 'track_code');

        foreach (self::QUESTIONS as $index => $data) {
            $question = PlacementQuestion::create([
                'question' => $data['question'],
                'order' => $index,
            ]);

            $order = 0;
            foreach ($data['options'] as $trackCode => $optionText) {
                if (! isset($tracks[$trackCode])) {
                    continue;
                }

                $question->options()->create([
                    'option_text' => $optionText,
                    'track_id' => $tracks[$trackCode],
                    'order' => $order++,
                ]);
            }
        }
    }
}
