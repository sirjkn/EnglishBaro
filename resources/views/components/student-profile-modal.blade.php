@props(['studentProfile'])

@php
    $user = auth()->user();
    $profileFields = ['name', 'gender', 'phone', 'country', 'referral_email', 'avatar'];
@endphp

<div x-data="{ open: {{ $errors->hasAny($profileFields) ? 'true' : 'false' }}, preview: null }" {{ $attributes }}>
    <button type="button" @click="open = true" class="flex items-center gap-1 rounded-md border border-blue-600 bg-white px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-50 dark:border-blue-400 dark:bg-gray-800 dark:text-blue-300 dark:hover:bg-gray-700">
        <x-icons.pencil class="h-3 w-3" /> Edit Profile
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 sm:items-center" @click.self="open = false">
            <div x-show="open" x-transition class="relative my-8 w-full max-w-2xl rounded-xl border-2 border-indigo-300 bg-white p-6 shadow-xl dark:border-indigo-700 dark:bg-gray-800" role="dialog" aria-modal="true" aria-labelledby="edit-profile-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="edit-profile-title" class="text-lg font-bold text-gray-900 dark:text-white">Edit Profile</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Student ID: {{ $studentProfile?->student_id }}</p>
                    </div>
                    <button type="button" @click="open = false" aria-label="Close" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('student.account.update') }}" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-6">
                        <div class="relative h-24 w-24 flex-none overflow-hidden rounded-lg border-2 border-indigo-200 dark:border-indigo-800">
                            <x-student-avatar :profile="$studentProfile" size="h-24 w-24" class="!rounded-lg" />
                            <img x-show="preview" x-cloak :src="preview" alt="Selected photo" class="absolute inset-0 h-full w-full object-cover">
                        </div>
                        <label class="cursor-pointer rounded-md border border-indigo-500 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-400 dark:bg-gray-800 dark:text-indigo-300 dark:hover:bg-indigo-900/30">
                            Change Photo
                            <input type="file" name="avatar" accept="image/*" class="sr-only" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </label>
                    </div>
                    @error('avatar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Names</label>
                            <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="profile_gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                            <select id="profile_gender" name="gender" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="">Not set</option>
                                <option value="male" @selected(old('gender', $studentProfile?->gender) === 'male')>Male</option>
                                <option value="female" @selected(old('gender', $studentProfile?->gender) === 'female')>Female</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Used only to pick a default avatar when you haven't uploaded a photo.</p>
                        </div>

                        <div>
                            <label for="profile_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input id="profile_email" type="email" value="{{ $user->email }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        </div>

                        <div>
                            <label for="profile_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input id="profile_phone" type="text" name="phone" value="{{ old('phone', $studentProfile?->phone) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="profile_country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                            <select id="profile_country" name="country" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="">Select Country</option>
                                @foreach (\App\Support\Countries::all() as $countryOption)
                                    <option value="{{ $countryOption }}" @selected(old('country', $studentProfile?->country) === $countryOption)>{{ $countryOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="profile_referral_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referral Email</label>
                            <input id="profile_referral_email" type="email" name="referral_email" value="{{ old('referral_email', $studentProfile?->referral_email) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            @error('referral_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="profile_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Level</label>
                            <input id="profile_level" type="text" value="{{ $studentProfile?->level?->name ?? 'Not set' }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        </div>

                        <div>
                            <label for="profile_member_since" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Member Since</label>
                            <input id="profile_member_since" type="text" value="{{ $user->created_at->format('j F Y') }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                            <p class="mt-1 text-xs text-gray-400">Set when you registered.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <button type="button" @click="open = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
