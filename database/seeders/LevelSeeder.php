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
            ['name' => 'Beginner', 'code' => 'A1', 'order' => 1, 'is_default' => true],
            ['name' => 'Elementary', 'code' => 'A2', 'order' => 2],
            ['name' => 'Pre-Intermediate', 'code' => 'B1', 'order' => 3],
            ['name' => 'Intermediate', 'code' => 'B2', 'order' => 4],
            ['name' => 'Upper-Intermediate', 'code' => 'C1', 'order' => 5],
            ['name' => 'Advanced', 'code' => 'C2', 'order' => 6],
        ];

        foreach ($levels as $level) {
            Level::query()->updateOrCreate(
                ['slug' => Str::slug($level['name'])],
                [
                    'name' => $level['name'],
                    'code' => $level['code'],
                    'order' => $level['order'],
                    'is_default' => $level['is_default'] ?? false,
                    'is_active' => true,
                ]
            );
        }
    }
}
