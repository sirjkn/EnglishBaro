<x-layouts.site :title="'Terms &amp; Conditions'">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Terms &amp; Conditions</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Last updated {{ now()->format('F Y') }}</p>

        <div class="prose prose-sm dark:prose-invert mt-8 max-w-none">
            <p>
                These terms cover your use of EnglishBaro. This is a placeholder — replace this copy with your
                organization's reviewed terms before relying on it for compliance purposes.
            </p>

            <h2>Enrollment &amp; access</h2>
            <p>
                Purchasing a track unlocks every level in that track for the subscription period shown on the
                track's page. Access expires at the end of that period unless renewed.
            </p>

            <h2>Payments</h2>
            <p>
                Prices are shown in the currency and region displayed at checkout. Payments are handled by our
                payment partners; we do not store full card details.
            </p>

            <h2>Acceptable use</h2>
            <p>
                Accounts are personal and may not be shared. Course content, videos and eBooks are for your
                personal learning use and may not be redistributed.
            </p>

            <h2>Changes</h2>
            <p>
                We may update these terms from time to time. Continued use of the platform after a change means
                you accept the updated terms.
            </p>

            <h2>Contact</h2>
            <p>
                Questions about these terms can be sent to
                {{ \App\Models\CompanySetting::get('support_email', 'support@englishbaro.test') }}.
            </p>
        </div>
    </div>
</x-layouts.site>
