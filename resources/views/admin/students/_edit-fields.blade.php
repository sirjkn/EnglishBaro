@php $student = $student ?? null; @endphp

<div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
    <div class="sm:col-span-2 lg:col-span-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Photo</label>
        <div class="mt-1 flex items-center gap-4">
            <x-student-avatar :profile="$student->studentProfile" size="h-16 w-16" />
            <input type="file" name="avatar" accept="image/*" class="text-sm text-gray-700 dark:text-gray-300">
        </div>
        @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Names</label>
        <input type="text" name="name" value="{{ old('name', $student->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
        <select name="gender" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">Not set</option>
            <option value="male" @selected(old('gender', $student->studentProfile?->gender) === 'male')>Male</option>
            <option value="female" @selected(old('gender', $student->studentProfile?->gender) === 'female')>Female</option>
        </select>
        <p class="mt-1 text-xs text-gray-400">Used only to pick a default avatar when no photo is uploaded.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $student->email) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-400">Changing this un-verifies the account — use "Verify Email" on the profile page afterward if needed.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $student->studentProfile?->phone) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
        <select name="country" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">Select Country</option>
            @foreach ($countries as $countryOption)
                <option value="{{ $countryOption }}" @selected(old('country', $student->studentProfile?->country) === $countryOption)>{{ $countryOption }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referral Email</label>
        <input type="email" name="referral_email" value="{{ old('referral_email', $student->studentProfile?->referral_email) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Track</label>
        <select name="track_id" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">Not set</option>
            @foreach ($tracks as $track)
                <option value="{{ $track->id }}" @selected(old('track_id', $student->studentProfile?->track_id) == $track->id)>{{ $track->track_code }} — {{ $track->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-end gap-2 pb-2">
        <input type="checkbox" name="is_active" value="1" id="is_active_{{ $student->id }}" @checked(old('is_active', $student->is_active)) class="rounded border-gray-300 text-indigo-600">
        <label for="is_active_{{ $student->id }}" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
    </div>
</div>
