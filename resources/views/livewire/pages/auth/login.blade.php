<?php

use App\Livewire\Forms\LoginForm;
use App\Services\SessionQuotaService;
use App\Support\SafeRedirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-split')] class extends Component
{
    public LoginForm $form;

    public ?string $sessionLimitMessage = null;

    #[Url(except: '')]
    public string $redirect = '';

    /**
     * Handle an incoming authentication request.
     */
    public function login(SessionQuotaService $sessionQuota): void
    {
        $this->sessionLimitMessage = null;

        $this->validate();

        $this->form->authenticate();

        $user = Auth::user();

        if ($user->isStudent() && $sessionQuota->exceedsMonthlyLimit($user)) {
            $this->sessionLimitMessage = $sessionQuota->blockedMessage($user);

            $sessionQuota->revokeLatestSession($user);

            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();

            return;
        }

        Session::regenerate();

        if ($this->redirect) {
            $this->redirect(SafeRedirect::resolve($this->redirect, route('dashboard', absolute: false)));

            return;
        }

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold tracking-wide text-gray-900 dark:text-white">LOGIN</h1>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Welcome back. Please enter your details.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    @if ($sessionLimitMessage)
        <div class="mt-4 flex items-start gap-3 rounded-lg border border-red-300 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/40">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $sessionLimitMessage }}</p>
        </div>
    @endif

    <form wire:submit="login" class="mt-6 space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="sr-only" />
            <x-text-input wire:model="form.email" id="email" class="block w-full" type="email" name="email" placeholder="Email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="sr-only" />

            <x-text-input wire:model="form.password" id="password" class="block w-full"
                            type="password"
                            name="password"
                            placeholder="Password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me / Forgot -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="w-full rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
            {{ __('Login Now') }}
        </button>
    </form>

    <div class="my-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        <span class="text-xs font-medium text-gray-400">Login with Others</span>
        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('auth.social.redirect', ['provider' => 'google', 'redirect' => $redirect ?: null]) }}"
           class="flex items-center justify-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            <x-icons.google />
            Google
        </a>
        <a href="{{ route('auth.social.redirect', ['provider' => 'facebook', 'redirect' => $redirect ?: null]) }}"
           class="flex items-center justify-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            <x-icons.facebook />
            Facebook
        </a>
    </div>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        {{ __("Don't have an account?") }}
    </p>
    <a href="{{ route('register.create', ['redirect' => $redirect ?: null]) }}" wire:navigate
       class="mt-2 block w-full rounded-md border border-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30">
        {{ __('Create an account here') }}
    </a>
</div>
