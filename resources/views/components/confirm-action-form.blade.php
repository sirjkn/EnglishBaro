@props([
    'action',
    'method' => 'POST',
    'label' => 'Confirm',
    'title' => 'Are you sure?',
    'message' => 'This action takes effect immediately.',
    'confirmLabel' => 'Yes, Continue',
    'class' => 'text-sm font-medium text-indigo-600 hover:text-indigo-500',
    'confirmClass' => 'rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500',
])

<div x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

            <div class="relative w-full max-w-sm rounded-xl border-2 border-indigo-200 bg-white p-6 shadow-xl dark:border-indigo-900 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">!</span>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="open = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @if (strtoupper($method) !== 'POST')
                            @method($method)
                        @endif
                        <button type="submit" class="{{ $confirmClass }}">
                            {{ $confirmLabel }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
