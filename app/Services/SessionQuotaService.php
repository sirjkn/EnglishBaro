<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;

class SessionQuotaService
{
    public const MONTHLY_LIMIT = 3;

    public function countThisMonth(User $user): int
    {
        return UserSession::query()
            ->where('user_id', $user->id)
            ->whereBetween('login_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->distinct('session_id')
            ->count('session_id');
    }

    public function exceedsMonthlyLimit(User $user): bool
    {
        return $this->countThisMonth($user) > self::MONTHLY_LIMIT;
    }

    /**
     * Undo the session row the login listener just created for a blocked attempt.
     */
    public function revokeLatestSession(User $user): void
    {
        $session = UserSession::query()
            ->where('user_id', $user->id)
            ->latest('login_at')
            ->first();

        if (! $session) {
            return;
        }

        DB::table('sessions')->where('id', $session->session_id)->delete();
        $session->delete();
    }

    public function activeSession(User $user): ?UserSession
    {
        return UserSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('last_activity_at')
            ->first();
    }

    public function daysUntilReset(): int
    {
        $resetsAt = now()->startOfMonth()->addMonthNoOverflow();

        return max(1, (int) ceil(now()->diffInHours($resetsAt) / 24));
    }

    public function blockedMessage(User $user): string
    {
        $days = $this->daysUntilReset();
        $active = $this->activeSession($user);

        $device = $active ? "{$active->device} ({$active->browser})" : 'your existing device';

        return "You've reached your monthly limit of ".self::MONTHLY_LIMIT." sessions. ".
            "Please log in from your active session on {$device}. ".
            "Your limit resets in {$days} day(s). Contact an administrator if you need an earlier reset.";
    }

    /**
     * Admin action: clear this month's session count so the student can log in again.
     */
    public function resetForCurrentMonth(User $user): int
    {
        $sessions = UserSession::query()
            ->where('user_id', $user->id)
            ->whereBetween('login_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('status', '!=', 'active')
            ->get();

        DB::table('sessions')->whereIn('id', $sessions->pluck('session_id'))->delete();

        $count = $sessions->count();

        UserSession::query()->whereIn('id', $sessions->pluck('id'))->delete();

        return $count;
    }
}
