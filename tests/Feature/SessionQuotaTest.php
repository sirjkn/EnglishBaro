<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SessionQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_is_blocked_after_three_sessions_this_month(): void
    {
        $user = User::factory()->create([
            'user_type' => 'student',
            'password' => bcrypt('password'),
        ]);

        // Simulate 3 already-used sessions this month.
        for ($i = 0; $i < 3; $i++) {
            UserSession::create([
                'session_id' => Str::random(40),
                'user_id' => $user->id,
                'device' => 'Desktop',
                'browser' => 'Chrome',
                'platform' => 'Windows',
                'ip_address' => '10.0.0.'.$i,
                'login_at' => now(),
                'last_activity_at' => now(),
                'status' => 'terminated',
            ]);
        }

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertSet('sessionLimitMessage', fn ($message) => ! empty($message));
        $this->assertGuest();
    }

    public function test_student_can_login_within_quota(): void
    {
        $user = User::factory()->create([
            'user_type' => 'student',
            'password' => bcrypt('password'),
        ]);

        UserSession::create([
            'session_id' => Str::random(40),
            'user_id' => $user->id,
            'device' => 'Desktop',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'ip_address' => '10.0.0.1',
            'login_at' => now(),
            'last_activity_at' => now(),
            'status' => 'terminated',
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_admin_is_not_subject_to_session_quota(): void
    {
        $user = User::factory()->create([
            'user_type' => 'admin',
            'password' => bcrypt('password'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            UserSession::create([
                'session_id' => Str::random(40),
                'user_id' => $user->id,
                'device' => 'Desktop',
                'browser' => 'Chrome',
                'platform' => 'Windows',
                'ip_address' => '10.0.0.'.$i,
                'login_at' => now(),
                'last_activity_at' => now(),
                'status' => 'terminated',
            ]);
        }

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors();
        $this->assertAuthenticated();
    }
}
