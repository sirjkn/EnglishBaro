<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $query = UserSession::query()->with('user.studentProfile');

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($device = $request->string('device')->value()) {
            $query->where('device', $device);
        }

        if ($ip = trim((string) $request->string('ip')->value())) {
            $query->where('ip_address', 'like', "%{$ip}%");
        }

        if ($student = trim((string) $request->string('student')->value())) {
            $query->whereHas('user', function ($userQuery) use ($student) {
                $userQuery->where('name', 'like', "%{$student}%")
                    ->orWhereHas('studentProfile', function ($profileQuery) use ($student) {
                        $profileQuery->where('student_id', 'like', "%{$student}%");
                    });
            });
        }

        $sessions = $query->latest('last_activity_at')->paginate(20)->withQueryString();

        return view('admin.sessions.index', [
            'sessions' => $sessions,
            'devices' => UserSession::query()->whereNotNull('device')->distinct()->orderBy('device')->pluck('device'),
        ]);
    }

    public function destroy(UserSession $session): RedirectResponse
    {
        $this->authorize('terminate', $session);

        AuditLogger::log('admin.session.terminated', $session);

        DB::table('sessions')->where('id', $session->session_id)->delete();

        // Deleted (not just marked terminated) so this slot no longer counts
        // toward the student's monthly session quota — an admin termination
        // frees them to log in again immediately, unlike a normal logout.
        $session->delete();

        return back()->with('status', 'Session terminated. This frees up one of the student\'s monthly session slots.');
    }
}
