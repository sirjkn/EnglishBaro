<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Track Code</label>
        @if ($creating ?? false)
            <input type="text" name="track_code" value="{{ old('track_code') }}" placeholder="e.g. C1" maxlength="10"
                   class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm uppercase dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <p class="mt-1 text-xs text-gray-400">A short unique code (letters/numbers/dashes only), e.g. C1, C2.</p>
            @error('track_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        @else
            <input type="text" value="{{ $track->track_code }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 font-mono text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            <p class="mt-1 text-xs text-gray-400">The track code can't be changed after creation.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
        <input type="text" name="name" value="{{ old('name', $track->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('description', $track->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Learning Outcomes</label>
        <textarea name="learning_outcomes" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('learning_outcomes', $track->learning_outcomes) }}</textarea>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $track->price) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', $track->currency) }}" maxlength="3" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subscription Days</label>
            <input type="number" min="1" name="subscription_days" value="{{ old('subscription_days', $track->subscription_days) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Regional Pricing (optional overrides)</label>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to fall back to the base price above for that region.</p>
        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400">Africa</label>
                <input type="number" step="0.01" min="0" name="price_africa" value="{{ old('price_africa', $track->price_africa) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400">Europe</label>
                <input type="number" step="0.01" min="0" name="price_europe" value="{{ old('price_europe', $track->price_europe) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400">North America</label>
                <input type="number" step="0.01" min="0" name="price_north_america" value="{{ old('price_north_america', $track->price_north_america) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400">Asia</label>
                <input type="number" step="0.01" min="0" name="price_asia" value="{{ old('price_asia', $track->price_asia) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="draft" @selected(old('status', $track->status) === 'draft')>Draft</option>
            <option value="published" @selected(old('status', $track->status) === 'published')>Published</option>
            <option value="archived" @selected(old('status', $track->status) === 'archived')>Archived</option>
        </select>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_featured" value="1" id="is_featured" @checked(old('is_featured', $track->is_featured)) class="rounded border-gray-300 text-indigo-600">
        <label for="is_featured" class="text-sm text-gray-700 dark:text-gray-300">Featured on homepage</label>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail</label>
        @if ($track->thumbnail)
            <img src="{{ $track->thumbnail->resolved_url }}" class="mt-2 h-24 w-24 rounded-lg object-cover">
        @endif
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a file, or provide an external image URL (Imgur supported).</p>
        <div class="mt-1 grid grid-cols-2 gap-3">
            <input type="file" name="thumbnail_file" accept="image/*" class="text-sm text-gray-600 dark:text-gray-300">
            <input type="url" name="thumbnail_url" placeholder="https://i.imgur.com/..." class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
    </div>
</div>
