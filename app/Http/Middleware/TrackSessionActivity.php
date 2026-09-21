<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            UserSession::query()
                ->where('session_id', session()->getId())
                ->where('status', 'active')
                ->update(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}
