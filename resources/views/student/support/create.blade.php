<x-layouts.student :title="'New Support Ticket'">
    <a href="{{ route('student.support.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; Support</a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">New Support Ticket</h1>

    <form method="POST" action="{{ route('student.support.store') }}" class="mt-6 max-w-xl space-y-4 rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
            <textarea name="message" rows="6" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" placeholder="Describe your issue...">{{ old('message') }}</textarea>
            @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit Ticket</button>
    </form>
</x-layouts.student>
