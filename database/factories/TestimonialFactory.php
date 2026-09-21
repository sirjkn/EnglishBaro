<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role_label' => fake()->randomElement(['Student', 'IELTS Candidate', 'Working Professional']),
            'content' => fake()->paragraph(),
            'rating' => fake()->numberBetween(4, 5),
            'is_published' => true,
            'order' => 0,
        ];
    }
}
