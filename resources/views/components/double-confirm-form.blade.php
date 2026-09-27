@props([
    'action',
    'method' => 'DELETE',
    'label' => 'Cancel Enrollment',
    'title' => 'Cancel this enrollment?',
    'message' => 'This action cannot be undone.',
    'confirmWord' => 'DELETE',
    'class' => 'text-sm font-medium text-red-600 hover:text-red-500',
])

<div x-data="{ step: 0, typed: '' }" class="inline">
    <button type="button" @click="step = 1" {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</button>

    <template x-teleport="body">
        {{-- Step 1: are you sure? --}}
        <div x-show="step === 1" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/50" @click="step = 0"></div>

            <div class="relative w-full max-w-sm rounded-xl border-2 border-red-300 bg-white p-6 shadow-xl dark:border-red-900 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl font-bold text-red-600 dark:bg-red-900/40 dark:text-red-300">!</span>
                    <h2 class="text-base font-semibold text-red-700 dark:text-red-300">{{ $title }}</h2>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="step = 0" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="button" @click="step = 2" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                        Continue
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 2: type the confirm word --}}
        <div x-show="step === 2" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/50" @click="step = 0; typed = ''"></div>

            <div class="relative w-full max-w-sm rounded-xl border-2 border-red-300 bg-white p-6 shadow-xl dark:border-red-900 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl font-bold text-red-600 dark:bg-red-900/40 dark:text-red-300">!</span>
                    <h2 class="text-base font-semibold text-red-700 dark:text-red-300">Final confirmation</h2>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Type <span class="font-mono font-bold text-red-600">{{ $confirmWord }}</span> below to confirm.
                </p>

                <input type="text" x-model="typed" autocomplete="off"
                       class="mt-3 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                       placeholder="Type {{ $confirmWord }}">

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="step = 0; typed = ''" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @if (strtoupper($method) !== 'POST')
                            @method($method)
                        @endif
                        <button type="submit" :disabled="typed !== '{{ $confirmWord }}'"
                                class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-40">
                            Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
