<div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
    <div class="sm:col-span-2 lg:col-span-3">
        <div x-data="{ preview: null }" class="flex items-center gap-8">
            <div class="relative h-28 w-28 flex-none overflow-hidden rounded-lg border-2 border-indigo-200 dark:border-indigo-800">
                <x-student-avatar size="h-28 w-28" class="!rounded-lg" />
                <img x-show="preview" x-cloak :src="preview" alt="Selected photo" class="absolute inset-0 h-full w-full object-cover">
            </div>
            <label class="cursor-pointer rounded-md border border-indigo-500 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-400 dark:bg-gray-800 dark:text-indigo-300 dark:hover:bg-indigo-900/30">
                Choose File
                <input type="file" name="avatar" accept="image/*" class="sr-only" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            </label>
        </div>
        @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2 lg:col-span-1">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Names</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
        <select name="gender" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">Not set</option>
            <option value="male" @selected(old('gender') === 'male')>Male</option>
            <option value="female" @selected(old('gender') === 'female')>Female</option>
        </select>
        <p class="mt-1 text-xs text-gray-400">Used only to pick a default avatar when no photo is uploaded.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
        <select name="country" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">Select Country</option>
            @foreach ($countries as $countryOption)
                <option value="{{ $countryOption }}" @selected(old('country') === $countryOption)>{{ $countryOption }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Temporary Password</label>
        <input type="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
