<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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

        $sessions = $query->latest('last_activity_at')->get();

        // Bundle sessions by user so the page reads as one row per student
        // (with an accordion revealing their individual sessions) instead of
        // a flat list repeating the same name across many rows.
        $groups = $sessions->groupBy('user_id')
            ->map(function ($userSessions) {
                return (object) [
                    'user' => $userSessions->first()->user,
                    'sessions' => $userSessions,
                    'activeCount' => $userSessions->where('status', 'active')->count(),
                    'lastActivityAt' => $userSessions->max('last_activity_at'),
                ];
            })
            ->sortByDesc('lastActivityAt')
            ->values();

        $page = (int) $request->query('page', 1);
        $perPage = 20;

        $groupedUsers = new LengthAwarePaginator(
            $groups->forPage($page, $perPage),
            $groups->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.sessions.index', [
            'groupedUsers' => $groupedUsers,
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

    /**
     * Terminates all of this user's active sessions except the one the admin
     * is currently browsing with — matters when an admin bulk-terminates
     * their own account's sessions and shouldn't log themselves out too.
     */
    public function destroyOthers(User $user): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $currentSessionId = Session::getId();

        $sessions = UserSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('session_id', '!=', $currentSessionId)
            ->get();

        DB::table('sessions')->whereIn('id', $sessions->pluck('session_id'))->delete();

        AuditLogger::log('admin.session.terminated_others', $user, [], ['count' => $sessions->count()]);

        UserSession::query()->whereIn('id', $sessions->pluck('id'))->delete();

        return back()->with('status', "Terminated {$sessions->count()} session(s) for {$user->name}.");
    }

    /**
     * Terminates every active student session, site-wide.
     */
    public function destroyAllStudents(): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $sessions = UserSession::query()
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('user_type', 'student'))
            ->get();

        DB::table('sessions')->whereIn('id', $sessions->pluck('session_id'))->delete();

        AuditLogger::log('admin.session.terminated_all_students', null, [], ['count' => $sessions->count()]);

        UserSession::query()->whereIn('id', $sessions->pluck('id'))->delete();

        return back()->with('status', "Terminated {$sessions->count()} student session(s).");
    }

    /**
     * Terminates every other admin session, site-wide — keeps whichever
     * session the acting admin is currently browsing with.
     */
    public function destroyAllAdmins(): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $currentSessionId = Session::getId();

        $sessions = UserSession::query()
            ->where('status', 'active')
            ->where('session_id', '!=', $currentSessionId)
            ->whereHas('user', fn ($query) => $query->where('user_type', 'admin'))
            ->get();

        DB::table('sessions')->whereIn('id', $sessions->pluck('session_id'))->delete();

        AuditLogger::log('admin.session.terminated_all_admins', null, [], ['count' => $sessions->count()]);

        UserSession::query()->whereIn('id', $sessions->pluck('id'))->delete();

        return back()->with('status', "Terminated {$sessions->count()} admin session(s). Your current session was kept.");
    }
}
