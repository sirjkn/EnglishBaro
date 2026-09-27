<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Services\TrackCurriculumGenerator;
use Illuminate\Database\Seeder;

class TrackCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $generator = app(TrackCurriculumGenerator::class);

        foreach (Track::query()->orderBy('order')->get() as $track) {
            $generator->generate($track);
        }
    }
}
