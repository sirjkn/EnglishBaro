<x-layouts.admin :title="'Edit Student'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Student</h1>

    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Names</label>
            <input type="text" name="name" value="{{ old('name', $student->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" value="{{ $student->email }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
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
