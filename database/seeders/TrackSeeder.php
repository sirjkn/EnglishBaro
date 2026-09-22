<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrackSeeder extends Seeder
{
    /**
     * Regional price multipliers relative to the base price, derived from the
     * reference pricing example (Africa $25 / Europe $40 / North America $45 / Asia $30).
     */
    private const REGION_MULTIPLIERS = [
        'price_africa' => 1.0,
        'price_europe' => 1.6,
        'price_north_america' => 1.8,
        'price_asia' => 1.2,
    ];

    public function run(): void
    {
        $tracks = [
            ['code' => 'A1', 'name' => 'Beginner', 'price' => 24.99, 'order' => 1, 'is_default' => true],
            ['code' => 'A2', 'name' => 'Elementary', 'price' => 29.99, 'order' => 2],
            ['code' => 'B1', 'name' => 'Pre-Intermediate', 'price' => 34.99, 'order' => 3],
            ['code' => 'B2', 'name' => 'Intermediate', 'price' => 39.99, 'order' => 4],
        ];

        foreach ($tracks as $data) {
            $regionalPrices = collect(self::REGION_MULTIPLIERS)
                ->mapWithKeys(fn ($multiplier, $column) => [$column => round($data['price'] * $multiplier, 2)])
                ->all();

            Track::query()->updateOrCreate(
                ['track_code' => $data['code']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['code'].'-'.$data['name']),
                    'description' => "The {$data['code']} ({$data['name']}) track: 100 levels of grammar, listening, speaking and reading, taught step by step.",
                    'learning_outcomes' => "By the end of the {$data['code']} track you will handle everyday {$data['name']} level English with confidence across all four skills.",
                    'price' => $data['price'],
                    'currency' => 'USD',
                    // One payment unlocks all 100 levels of the track for 120 days.
                    'subscription_days' => 120,
                    'status' => 'published',
                    'is_featured' => true,
                    'order' => $data['order'],
                    'is_default' => $data['is_default'] ?? false,
                    'is_active' => true,
                    ...$regionalPrices,
                ]
            );
        }
    }
}
