<x-layouts.student :title="'Sessions'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sessions</h1>

    <div x-data="{ dark: false }" x-init="try { dark = localStorage.getItem('theme') === 'dark' } catch (e) {}" class="mt-6 flex items-center justify-between rounded-xl border-2 border-indigo-300 bg-white p-4 dark:border-indigo-700 dark:bg-gray-800">
        <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">Dark Mode</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Off by default. This only affects your own browser.</p>
        </div>
        <button
            type="button"
            @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('theme', dark ? 'dark' : 'light') } catch (e) {}"
            :class="dark ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
            class="relative inline-flex h-6 w-11 flex-none items-center rounded-full transition-colors"
            role="switch"
            :aria-checked="dark.toString()"
        >
            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="dark ? 'translate-x-6' : 'translate-x-1'"></span>
        </button>
    </div>

    {{-- Sessions --}}
    <div class="mt-8 rounded-xl border-2 border-indigo-300 bg-white p-6 dark:border-indigo-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white">Sessions</h2>
            <form method="POST" action="{{ route('student.sessions.destroy-others') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md border border-red-600 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:bg-gray-800 dark:hover:bg-red-900/30">Logout other devices</button>
            </form>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                <thead>
                    <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                        <th class="py-2 pr-4">Device</th>
                        <th class="py-2 pr-4">Browser</th>
                        <th class="py-2 pr-4">IP</th>
                        <th class="py-2 pr-4">Last Activity</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                    @foreach ($sessions as $session)
                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->device }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->browser }} &middot; {{ $session->platform }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->ip_address }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                            <td class="py-2 pr-4">
                                <x-badge :pill="false" :color="$session->status === 'active' ? 'green' : 'gray'">{{ ucfirst($session->status) }}</x-badge>
                            </td>
                            <td class="py-2 text-right">
                                @if ($session->status === 'active')
                                    <form method="POST" action="{{ route('student.sessions.destroy', $session) }}" onsubmit="return confirm('Terminate this session?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-700 bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-500">Terminate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="mt-8 rounded-xl border-2 border-indigo-300 bg-white p-6 dark:border-indigo-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">Recent Payments</h2>

        @if ($payments->isEmpty())
            <x-empty-state class="mt-4" message="No payment history yet." />
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                            <th class="py-2 pr-4">Transaction</th>
                            <th class="py-2 pr-4">Amount</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                        @foreach ($payments as $payment)
                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-2 pr-4"><x-payment-status :status="$payment->status" /></td>
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.student>
