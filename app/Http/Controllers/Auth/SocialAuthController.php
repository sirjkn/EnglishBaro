<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterOauthUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, RegisterOauthUserAction $registerOauthUserAction): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

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

        return redirect()->route('dashboard');
    }

    private function ensureProviderIsSupported(string $provider): void
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), Response::HTTP_NOT_FOUND);
    }
}
