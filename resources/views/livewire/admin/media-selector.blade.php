<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</p>

    @if ($current)
        <div class="mt-3 flex items-center gap-3">
            @if ($current->type === 'image')
                <img src="{{ $current->resolved_url }}" alt="{{ $current->title }}" class="h-16 w-16 rounded-lg object-cover">
            @elseif ($current->type === 'video' && $current->provider === 'youtube')
                <div class="flex h-16 w-24 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-500 dark:bg-gray-800">YouTube</div>
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-500 dark:bg-gray-800">{{ strtoupper($current->type) }}</div>
            @endif
            <div class="flex-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $current->original_filename ?? $current->url }}
            </div>
            <button type="button" wire:click="remove" class="text-xs font-medium text-red-600 hover:text-red-500">Remove</button>
        </div>
    @else
        <div class="mt-3">
            <div class="flex gap-2 text-xs">
                <button type="button" wire:click="$set('source', 'upload')" class="rounded-md px-3 py-1.5 font-medium {{ $source === 'upload' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                    Upload File
                </button>
                <button type="button" wire:click="$set('source', 'external')" class="rounded-md px-3 py-1.5 font-medium {{ $source === 'external' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                    External URL {{ $type === 'video' ? '/ YouTube' : '' }}
                </button>
            </div>

            @if ($source === 'upload')
                <div class="mt-3">
                    <input type="file" wire:model="file" class="block w-full text-sm text-gray-600 dark:text-gray-300">
                    @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="file" class="mt-1 text-xs text-gray-400">Uploading...</div>
                </div>
            @else
                <div class="mt-3">
                    <input type="url" wire:model="externalUrl" placeholder="https://..." class="block w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @error('url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <button type="button" wire:click="attach" class="mt-3 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                Attach Media
            </button>
        </div>
    @endif
</div>
