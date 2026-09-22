<?php

namespace App\Actions\Auth;

use App\Models\Track;
use App\Models\OauthAccount;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\StudentIdGenerator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class RegisterOauthUserAction
{
    public function __construct(
        private readonly StudentIdGenerator $studentIdGenerator,
    ) {}

    public function execute(string $provider, SocialiteUser $socialiteUser): User
    {
        $existingAccount = OauthAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $socialiteUser->getId())
            ->first();

        if ($existingAccount) {
            return $existingAccount->user;
        }

        return DB::transaction(function () use ($provider, $socialiteUser) {
            $user = User::query()->where('email', $socialiteUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name' => $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'New Student',
                    'email' => $socialiteUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'user_type' => 'student',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $defaultTrack = Track::query()->where('is_default', true)->first()
                    ?? Track::query()->orderBy('order')->first();

                StudentProfile::create([
                    'user_id' => $user->id,
                    'student_id' => $this->generateUniqueStudentId(),
                    'track_id' => $defaultTrack?->id,
                ]);

                $studentRole = Role::query()->where('slug', 'student')->first();

                if ($studentRole) {
                    $user->roles()->attach($studentRole->id);
                }

                event(new Registered($user));
            }

            OauthAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $socialiteUser->getId(),
                'avatar' => $socialiteUser->getAvatar(),
            ]);

            return $user;
        });
    }

    private function generateUniqueStudentId(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = $this->studentIdGenerator->generate();

            if (! StudentProfile::query()->where('student_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $this->studentIdGenerator->generate().'-'.uniqid();
    }
}
