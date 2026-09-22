<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\PaymentSetting;
use App\Models\SystemSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const COMPANY_KEYS = [
        'company_name', 'address', 'phone', 'email', 'website',
        'support_email', 'footer_text', 'copyright',
    ];

    private const SYSTEM_KEYS = [
        'default_subscription_days', 'site_maintenance_mode',
    ];

    private const GATEWAYS = ['flutterwave', 'mpesa', 'paypal', 'waafipay'];

    public function edit(): View
    {
        $company = CompanySetting::query()->pluck('value', 'key');
        $system = SystemSetting::query()->pluck('value', 'key');
        $paymentSettings = PaymentSetting::query()->get()->keyBy('gateway');

        return view('admin.settings.edit', [
            'company' => $company,
            'system' => $system,
            'paymentSettings' => $paymentSettings,
            'gateways' => self::GATEWAYS,
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'copyright' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            CompanySetting::set($key, $value);
        }

        AuditLogger::log('admin.settings.company_updated', null, [], $validated);

        return redirect()->route('admin.settings.edit', ['tab' => 'company'])->with('status', 'Company settings updated.');
    }

    public function updatePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gateway' => ['required', 'in:flutterwave,mpesa,paypal,waafipay'],
            'is_enabled' => ['boolean'],
            'is_sandbox' => ['boolean'],
            'config' => ['nullable', 'array'],
        ]);

        PaymentSetting::query()->updateOrCreate(
            ['gateway' => $validated['gateway']],
            [
                'is_enabled' => $request->boolean('is_enabled'),
                'is_sandbox' => $request->boolean('is_sandbox'),
                'config' => $validated['config'] ?? [],
            ]
        );

        AuditLogger::log('admin.settings.payment_updated', null, [], ['gateway' => $validated['gateway']]);

        return redirect()->route('admin.settings.edit', ['tab' => 'payment'])->with('status', ucfirst($validated['gateway']).' settings updated.');
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_subscription_days' => ['nullable', 'integer', 'min:1'],
            'site_maintenance_mode' => ['nullable', 'string'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, (string) $value);
        }

        AuditLogger::log('admin.settings.system_updated', null, [], $validated);

        return redirect()->route('admin.settings.edit', ['tab' => 'system'])->with('status', 'System settings updated.');
    }
}
