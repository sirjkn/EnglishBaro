<?php

namespace Database\Factories;

use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    public function definition(): array
    {
        $code = fake()->unique()->randomElement(Track::CODES);
        $name = match ($code) {
            'A1' => 'Beginner',
            'A2' => 'Elementary',
            'B1' => 'Pre-Intermediate',
            default => 'Intermediate',
        };

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'track_code' => $code,
            'description' => fake()->paragraph(),
            'learning_outcomes' => fake()->paragraph(),
            'price' => fake()->randomElement([19.99, 29.99, 39.99, 49.99]),
            'currency' => 'USD',
            'subscription_days' => 120,
            'status' => 'published',
            'is_featured' => true,
            'order' => fake()->unique()->numberBetween(1, 4),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
