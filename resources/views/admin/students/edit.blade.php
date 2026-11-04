<x-layouts.admin :title="'Edit Student'">
    <a href="{{ \App\Support\SafeRedirect::resolve($redirectTo, route('admin.students.show', $student)) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; Back to Profile</a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit Student</h1>

    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 max-w-lg space-y-4" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if ($redirectTo)
            <input type="hidden" name="redirect" value="{{ $redirectTo }}">
        @endif

        <div>
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

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $student->is_active)) class="rounded border-gray-300 text-indigo-600">
            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
        </div>

        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
    </form>
</x-layouts.admin>
