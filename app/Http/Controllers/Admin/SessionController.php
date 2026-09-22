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

        $query = UserSession::query()->with('user');

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $sessions = $query->latest('last_activity_at')->paginate(20)->withQueryString();

        return view('admin.sessions.index', ['sessions' => $sessions]);
    }

    public function destroy(UserSession $session): RedirectResponse
    {
        $this->authorize('terminate', $session);

        $session->update(['status' => 'terminated', 'logout_at' => now()]);

        DB::table('sessions')->where('id', $session->session_id)->delete();

        AuditLogger::log('admin.session.terminated', $session);

        return back()->with('status', 'Session terminated.');
    }
}
