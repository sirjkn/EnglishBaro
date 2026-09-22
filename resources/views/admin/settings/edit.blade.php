<x-layouts.admin :title="'Settings'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">Company Settings</h2>
        <form method="POST" action="{{ route('admin.settings.company') }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            @foreach ([
                'company_name' => 'Company Name',
                'address' => 'Address',
                'phone' => 'Phone',
                'email' => 'Email',
                'website' => 'Website',
                'support_email' => 'Support Email',
                'footer_text' => 'Footer Text',
                'copyright' => 'Copyright',
            ] as $key => $label)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                    <input type="text" name="{{ $key }}" value="{{ old($key, $company[$key] ?? '') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>
            @endforeach

            <div class="sm:col-span-2">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Company Settings</button>
            </div>
        </form>
    </div>

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">Payment Gateways</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Credentials are encrypted at rest and never exposed to the frontend.</p>

        <div class="mt-4 space-y-6">
            @foreach ($gateways as $gateway)
                @php $setting = $paymentSettings[$gateway] ?? null; @endphp
                <form method="POST" action="{{ route('admin.settings.payment') }}" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="gateway" value="{{ $gateway }}">

                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ ucfirst($gateway) }}</h3>
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_enabled" value="1" @checked($setting?->is_enabled) class="rounded border-gray-300 text-indigo-600">
                            Enabled
                        </label>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_sandbox" value="1" @checked($setting?->is_sandbox ?? true) class="rounded border-gray-300 text-indigo-600">
                            Sandbox Mode
                        </label>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <input type="text" name="config[public_key]" placeholder="Public / Consumer Key" value="{{ $setting?->config['public_key'] ?? '' }}" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <input type="password" name="config[secret_key]" placeholder="Secret Key" value="{{ $setting?->config['secret_key'] ?? '' }}" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <button type="submit" class="mt-3 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Save {{ ucfirst($gateway) }} Settings</button>
                </form>
            @endforeach
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">System Settings</h2>
        <form method="POST" action="{{ route('admin.settings.system') }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Subscription Days</label>
                <input type="number" name="default_subscription_days" min="1" value="{{ old('default_subscription_days', $system['default_subscription_days'] ?? 120) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save System Settings</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
