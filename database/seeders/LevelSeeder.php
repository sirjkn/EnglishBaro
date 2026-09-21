<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Beginner', 'order' => 1, 'is_default' => true],
            ['name' => 'Elementary', 'order' => 2],
            ['name' => 'Pre-Intermediate', 'order' => 3],
            ['name' => 'Intermediate', 'order' => 4],
            ['name' => 'Upper-Intermediate', 'order' => 5],
            ['name' => 'Advanced', 'order' => 6],
        ];

        foreach ($levels as $level) {
            Level::query()->updateOrCreate(
                ['slug' => Str::slug($level['name'])],
                [
                    'name' => $level['name'],
                    'order' => $level['order'],
                    'is_default' => $level['is_default'] ?? false,
                    'is_active' => true,
                ]
            );
        }
    }
}
