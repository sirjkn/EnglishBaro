<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TrackSeeder::class,
            TrackCurriculumSeeder::class,
            PlacementTestSeeder::class,
            AdminUserSeeder::class,
            DemoStudentSeeder::class,
            DemoEnrollmentSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
