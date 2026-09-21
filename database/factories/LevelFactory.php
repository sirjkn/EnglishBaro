<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Beginner', 'Elementary', 'Pre-Intermediate', 'Intermediate', 'Upper-Intermediate', 'Advanced',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'order' => fake()->unique()->numberBetween(1, 6),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
