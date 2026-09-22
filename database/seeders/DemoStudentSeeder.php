<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoStudentSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTrack = Track::query()->where('is_default', true)->first() ?? Track::query()->first();
        $studentRole = Role::query()->where('slug', 'student')->first();

        $student = User::query()->updateOrCreate(
            ['email' => 'student@englishbaro.test'],
            [
                'name' => 'Demo Student',
                'password' => 'password',
                'user_type' => 'student',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($studentRole && ! $student->roles->contains($studentRole->id)) {
            $student->roles()->attach($studentRole->id);
        }

        StudentProfile::query()->updateOrCreate(
            ['user_id' => $student->id],
            [
                'student_id' => 'EB-00001',
                'phone' => '+254700000000',
                'country' => 'Kenya',
                'track_id' => $defaultTrack?->id,
            ]
        );
    }
}
