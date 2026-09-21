<?php

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-split')] class extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $country = '';
    public string $referral_email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(RegisterUserAction $registerUserAction): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'country' => ['required', 'string', 'max:100'],
            'referral_email' => ['nullable', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $registerUserAction->execute([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country' => $validated['country'],
            'referral_email' => $validated['referral_email'] ?: null,
            'password' => $validated['password'],
        ]);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold tracking-wide text-gray-900 dark:text-white">JOIN ENGLISHBARO</h1>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create your account to start learning.</p>

    <form wire:submit="register" class="mt-6 space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Names')" class="sr-only" />
            <x-text-input wire:model="name" id="name" class="block w-full" type="text" name="name" placeholder="Full Names" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div>
            <x-input-label for="phone" :value="__('Phone')" class="sr-only" />
            <x-text-input wire:model="phone" id="phone" class="block w-full" type="text" name="phone" placeholder="Phone" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="sr-only" />
            <x-text-input wire:model="email" id="email" class="block w-full" type="email" name="email" placeholder="Email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Country -->
        <div>
            <x-input-label for="country" :value="__('Country')" class="sr-only" />
            <x-text-input wire:model="country" id="country" class="block w-full" type="text" name="country" placeholder="Country" required autocomplete="country-name" />
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <!-- Referral Email -->
        <div>
            <x-input-label for="referral_email" :value="__('Referral Email (optional)')" class="sr-only" />
            <x-text-input wire:model="referral_email" id="referral_email" class="block w-full" type="email" name="referral_email" placeholder="Referral Email (optional)" autocomplete="off" />
            <x-input-error :messages="$errors->get('referral_email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="sr-only" />

            <x-text-input wire:model="password" id="password" class="block w-full"
                            type="password"
                            name="password"
                            placeholder="Password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="sr-only" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full"
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm Password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
            {{ __('Register Now') }}
        </button>
    </form>

    <div class="my-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        <span class="text-xs font-medium text-gray-400">Register with Others</span>
        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('auth.social.redirect', 'google') }}"
           class="flex items-center justify-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            Google
        </a>
        <a href="{{ route('auth.social.redirect', 'facebook') }}"
           class="flex items-center justify-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            Facebook
        </a>
    </div>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        {{ __('Already registered?') }}
    </p>
    <a href="{{ route('login') }}" wire:navigate
       class="mt-2 block w-full rounded-md border border-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30">
        {{ __('Login here') }}
    </a>
</div>
