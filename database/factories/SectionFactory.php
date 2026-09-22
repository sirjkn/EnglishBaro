<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(Section::TYPES);

        return [
            'level_id' => Level::factory(),
            'type' => $type,
            'title' => Section::titleFor($type),
            'description' => fake()->sentence(),
            'order' => array_search($type, Section::TYPES, true),
        ];
    }
}
