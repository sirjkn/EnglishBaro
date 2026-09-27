@props(['track', 'label' => 'Pay', 'class' => 'rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500'])

@php
    $gateways = [
        'flutterwave' => ['label' => 'Flutterwave', 'color' => 'bg-orange-500'],
        'mpesa' => ['label' => 'M-Pesa', 'color' => 'bg-green-600'],
        'paypal' => ['label' => 'PayPal', 'color' => 'bg-blue-600'],
        'waafipay' => ['label' => 'WaafiPay', 'color' => 'bg-teal-600'],
    ];
@endphp

<div x-data="{ open: false, submitting: false }" class="inline">
    <button type="button" @click="open = true" {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

            <div class="relative w-full max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Choose a payment method</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $track->track_code }} &middot; {{ $track->currency }} {{ number_format($track->defaultPrice(), 2) }}
                </p>

                <div class="mt-4 space-y-2">
                    @foreach ($gateways as $key => $gateway)
                        <form method="POST" action="{{ route('student.tracks.pay', $track) }}" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="gateway" value="{{ $key }}">
                            <button type="submit" :disabled="submitting"
                                    class="flex w-full items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-left text-sm font-medium text-gray-700 hover:border-indigo-400 hover:bg-indigo-50 disabled:opacity-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $gateway['color'] }} text-xs font-bold text-white">
                                    {{ mb_substr($gateway['label'], 0, 1) }}
                                </span>
                                {{ $gateway['label'] }}
                            </button>
                        </form>
                    @endforeach
                </div>

                <p class="mt-4 text-center text-xs text-gray-400" x-show="submitting">Processing payment&hellip;</p>

                <button type="button" @click="open = false" class="mt-4 w-full rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancel
                </button>
            </div>
        </div>
    </template>
</div>
