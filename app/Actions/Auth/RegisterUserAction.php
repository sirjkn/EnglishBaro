<?php

namespace App\Actions\Auth;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\StudentIdGenerator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        private readonly StudentIdGenerator $studentIdGenerator,
    ) {}

    /**
     * @param  array{name: string, email: string, phone: string, country: string, referral_email: ?string, password: string}  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'user_type' => 'student',
                'is_active' => true,
            ]);

            $studentId = $this->generateUniqueStudentId();

            // track_id stays null until the student completes their placement test.
            StudentProfile::create([
                'user_id' => $user->id,
                'student_id' => $studentId,
                'phone' => $data['phone'],
                'country' => $data['country'],
                'referral_email' => $data['referral_email'] ?? null,
            ]);

            $studentRole = Role::query()->where('slug', 'student')->first();

            if ($studentRole) {
                $user->roles()->attach($studentRole->id);
            }

            event(new Registered($user));

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
