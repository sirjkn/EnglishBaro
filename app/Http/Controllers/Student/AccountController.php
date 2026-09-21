<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        $payments = $user->payments()->latest()->take(10)->get();

        return view('student.account', [
            'user' => $user,
            'studentProfile' => $user->studentProfile,
            'sessions' => $user->userSessions()->orderByDesc('last_activity_at')->get(),
            'countries' => Countries::all(),
            'payments' => $payments,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'referral_email' => ['nullable', 'string', 'email', 'max:255'],
        ]);

        $user->update(['name' => $validated['name']]);

        $user->studentProfile()->update([
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'referral_email' => $validated['referral_email'] ?? null,
        ]);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password updated successfully.');
    }
}
