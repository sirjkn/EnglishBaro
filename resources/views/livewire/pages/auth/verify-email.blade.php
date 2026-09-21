<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-split')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/');
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold tracking-wide text-gray-900 dark:text-white">CHECK YOUR EMAIL</h1>

    @if (session('status') && session('status') !== 'verification-link-sent')
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between">
        <button wire:click="sendVerification" class="rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
            {{ __('Resend Verification Email') }}
        </button>

        <button wire:click="logout" type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
            {{ __('Log Out') }}
        </button>
    </div>
</div>
