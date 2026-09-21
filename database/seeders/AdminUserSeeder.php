<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@englishbaro.test'],
            [
                'name' => 'EnglishBaro Admin',
                'password' => 'password',
                'user_type' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $adminRole = Role::query()->where('slug', 'admin')->first();

        if ($adminRole && ! $admin->roles->contains($adminRole->id)) {
            $admin->roles()->attach($adminRole->id);
        }
    }
}
