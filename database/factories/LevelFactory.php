<?php

namespace Database\Factories;

use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 100);

        return [
            'track_id' => Track::factory(),
            'number' => $number,
            'title' => "Level {$number}",
        ];
    }
}
