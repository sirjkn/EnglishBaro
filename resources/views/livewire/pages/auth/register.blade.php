<?php

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use App\Support\CountryCodes;
use App\Support\Countries;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-split')] class extends Component
{
    public string $name = '';
    public string $phone_country_code = '+254';
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
            'phone_country_code' => ['required', 'string', 'max:5'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'country' => ['required', 'string', 'max:100'],
            'referral_email' => ['nullable', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $registerUserAction->execute([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone_country_code'].' '.$validated['phone'],
            'country' => $validated['country'],
            'referral_email' => $validated['referral_email'] ?: null,
            'password' => $validated['password'],
        ]);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    #[Computed]
    public function countries(): array
    {
        return Countries::all();
    }

    #[Computed]
    public function countryCodes(): array
    {
        return CountryCodes::all();
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold tracking-wide text-gray-900 dark:text-white">JOIN ENGLISHBARO</h1>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create your account to start learning.</p>

    <form wire:submit="register" class="mt-5 space-y-3">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Names')" class="sr-only" />
            <x-text-input wire:model="name" id="name" class="block w-full text-sm py-1.5 px-2.5" type="text" name="name" placeholder="Full Names" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Phone -->
        <div>
            <x-input-label for="phone" :value="__('Phone')" class="sr-only" />
            <div class="flex gap-2">
                <select wire:model="phone_country_code" id="phone_country_code" name="phone_country_code" required
                        class="w-28 shrink-0 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm py-1.5 px-2 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                    @foreach ($this->countryCodes as $code)
                        <option value="{{ $code['dial_code'] }}">{{ $code['dial_code'] }} {{ $code['name'] }}</option>
                    @endforeach
                </select>
                <x-text-input wire:model="phone" id="phone" class="block w-full text-sm py-1.5 px-2.5" type="text" name="phone" placeholder="Phone number" required autocomplete="tel" />
            </div>
            <x-input-error :messages="$errors->get('phone_country_code')" class="mt-1" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="sr-only" />
            <x-text-input wire:model="email" id="email" class="block w-full text-sm py-1.5 px-2.5" type="email" name="email" placeholder="Email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Country -->
        <div>
            <x-input-label for="country" :value="__('Country')" class="sr-only" />
            <select wire:model="country" id="country" name="country" required autocomplete="country-name"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm py-1.5 px-2.5 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                <option value="">Select Country</option>
                @foreach ($this->countries as $countryOption)
                    <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('country')" class="mt-1" />
        </div>

        <!-- Referral Email -->
        <div>
            <x-input-label for="referral_email" :value="__('Referral Email (optional)')" class="sr-only" />
            <x-text-input wire:model="referral_email" id="referral_email" class="block w-full text-sm py-1.5 px-2.5" type="email" name="referral_email" placeholder="Referral Email (optional)" autocomplete="off" />
            <x-input-error :messages="$errors->get('referral_email')" class="mt-1" />
        </div>

        <!-- Password / Confirm Password -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <x-input-label for="password" :value="__('Password')" class="sr-only" />

                <x-text-input wire:model="password" id="password" class="block w-full text-sm py-1.5 px-2.5"
                                type="password"
                                name="password"
                                placeholder="Password"
                                required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="sr-only" />

                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full text-sm py-1.5 px-2.5"
                                type="password"
                                name="password_confirmation"
                                placeholder="Confirm Password"
                                required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <button type="submit" class="w-full rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
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
            <x-icons.google />
            Google
        </a>
        <a href="{{ route('auth.social.redirect', 'facebook') }}"
           class="flex items-center justify-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            <x-icons.facebook />
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
