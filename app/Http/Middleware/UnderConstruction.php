<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnderConstruction
{
    /**
     * Routes that must stay reachable even while the site is gated, so an
     * admin actually has a way to sign in and lift the gate.
     */
    private const ALLOWED_ROUTES = ['login', 'logout'];

    /**
     * A demo account that stays reachable even while the gate is on, so the
     * site can be shown to someone without lifting maintenance mode for
     * everyone.
     */
    private const DEMO_EMAILS = ['student@englishbaro.test'];

    public function handle(Request $request, Closure $next): Response
    {
        if (SystemSetting::get('site_maintenance_mode') !== '1') {
            return $next($request);
        }

        // Livewire/Volt components make their own background AJAX calls to
        // this internal protocol endpoint for every interaction (typing,
        // submitting, etc). It must never be gated: a non-200 response here
        // doesn't navigate anywhere, it just gets dumped into Livewire's own
        // failure modal on top of whatever page the user is already on.
        if ($request->is('livewire/*')) {
            return $next($request);
        }

        if ($request->user()?->isAdmin()) {
            return $next($request);
        }

        if (in_array($request->user()?->email, self::DEMO_EMAILS, true)) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        // Livewire's wire:navigate intercepts link clicks and fetches pages
        // via AJAX, then swaps document.body with the response. Our gate
        // page has a completely different <head> (no CSRF meta, no Vite
        // assets) to the rest of the app, and that mismatch can break
        // Livewire's head-merge step mid-way, leaving the old page's body
        // only partially replaced. Side-stepping that entirely is more
        // reliable than relying on the swap behaving correctly: respond
        // with a script that forces a real top-level navigation, so the
        // final render is always a fresh document load, not a DOM merge.
        if ($request->hasHeader('X-Livewire-Navigate')) {
            return response(
                '<script>window.location.replace('.json_encode($request->fullUrl()).');</script>',
                200,
                ['Content-Type' => 'text/html']
            );
        }

        return response()->view('under-construction', [], 503);
    }
}
