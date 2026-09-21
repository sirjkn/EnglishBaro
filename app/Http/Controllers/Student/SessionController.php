<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function destroy(UserSession $session): RedirectResponse
    {
        $this->authorize('terminate', $session);

        $isCurrentSession = $session->session_id === Session::getId();

        $session->update([
            'status' => 'terminated',
            'logout_at' => now(),
        ]);

        // Force-invalidate the underlying framework session so the other
        // device is actually logged out on its next request.
        DB::table('sessions')->where('id', $session->session_id)->delete();

        if ($isCurrentSession) {
            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();

            return redirect()->route('home');
        }

        return back()->with('status', 'Session terminated.');
    }

    public function destroyOthers(): RedirectResponse
    {
        $currentSessionId = Session::getId();

        $otherSessions = UserSession::query()
            ->where('user_id', auth()->id())
            ->where('session_id', '!=', $currentSessionId)
            ->where('status', 'active')
            ->get();

        DB::table('sessions')
            ->whereIn('id', $otherSessions->pluck('session_id'))
            ->delete();

        UserSession::query()
            ->whereIn('id', $otherSessions->pluck('id'))
            ->update(['status' => 'terminated', 'logout_at' => now()]);

        return back()->with('status', 'All other sessions have been logged out.');
    }
}
