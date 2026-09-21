<x-layouts.site :title="'Contact'">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Contact Us</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">We would love to hear from you.</p>

        <div class="mt-10 grid grid-cols-1 gap-12 lg:grid-cols-2">
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</h3>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ \App\Models\CompanySetting::get('address', 'Nairobi, Kenya') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</h3>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ \App\Models\CompanySetting::get('phone', '+000 000 0000') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</h3>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ \App\Models\CompanySetting::get('support_email', 'support@englishbaro.test') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Working Hours</h3>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ \App\Models\CompanySetting::get('working_hours', 'Mon - Fri, 9:00 AM - 5:00 PM') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone (optional)</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                    <textarea name="message" id="message" rows="5" required
                              class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</x-layouts.site>
