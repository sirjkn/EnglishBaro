<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use App\Support\PhoneNumberFormatter;
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

        return view('student.account', [
            'user' => $user,
            'studentProfile' => $user->studentProfile,
            'sessions' => $user->userSessions()->orderByDesc('last_activity_at')->get(),
        ]);
    }

    public function update(Request $request, MediaService $mediaService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female'],
            'referral_email' => ['nullable', 'string', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);

        $user->update(['name' => $validated['name']]);

        $profileUpdates = [
            'phone' => PhoneNumberFormatter::normalize($validated['phone'] ?? null, $validated['country'] ?? $user->studentProfile?->country),
            'country' => $validated['country'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'referral_email' => $validated['referral_email'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $profileUpdates['profile_image_id'] = $mediaService->storeUploadedFile($request->file('avatar'), 'image')->id;
        }

        $user->studentProfile()->update($profileUpdates);

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
