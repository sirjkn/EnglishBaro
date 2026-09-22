<?php

namespace Database\Factories;

use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'track_id' => Track::factory(),
            'status' => 'active',
            'enrolled_at' => now(),
        ];
    }
}
