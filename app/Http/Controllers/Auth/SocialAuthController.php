<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterOauthUserAction;
use App\Http\Controllers\Controller;
use App\Services\SessionQuotaService;
use App\Support\SafeRedirect;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'facebook'];

    private const SESSION_KEY = 'oauth_redirect';

    public function redirect(string $provider, Request $request): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        if ($redirect = $request->string('redirect')->toString()) {
            session()->put(self::SESSION_KEY, SafeRedirect::resolve($redirect, route('dashboard', absolute: false)));
        } else {
            session()->forget(self::SESSION_KEY);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, RegisterOauthUserAction $registerOauthUserAction, SessionQuotaService $sessionQuota): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        $intended = session()->pull(self::SESSION_KEY, route('dashboard', absolute: false));

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::warning('OAuth callback failed', ['provider' => $provider, 'error' => $e->getMessage()]);

            return redirect()->route('login')->with('status', __('Unable to authenticate with :provider. Please try again.', ['provider' => ucfirst($provider)]));
        }

        $user = $registerOauthUserAction->execute($provider, $socialiteUser);

        if (! $user->is_active) {
            return redirect()->route('login')->with('status', __('Your account has been deactivated.'));
        }

        Auth::login($user, remember: true);

        if ($user->isStudent() && $sessionQuota->exceedsMonthlyLimit($user)) {
            $message = $sessionQuota->blockedMessage($user);

            $sessionQuota->revokeLatestSession($user);

            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();

            return redirect()->route('login')->with('status', $message);
        }

        return redirect()->to($intended);
    }

    private function ensureProviderIsSupported(string $provider): void
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), Response::HTTP_NOT_FOUND);
    }
}
