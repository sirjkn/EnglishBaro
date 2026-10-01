<x-layouts.site :title="'Data Privacy'">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Privacy</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Last updated {{ now()->format('F Y') }}</p>

        <div class="prose prose-sm dark:prose-invert mt-8 max-w-none">
            <p>
                This page explains, in plain terms, what information EnglishBaro collects from students and
                visitors and how it is used. It is a placeholder — replace this copy with your organization's
                reviewed privacy policy before relying on it for compliance purposes.
            </p>

            <h2>What we collect</h2>
            <p>
                Account details you provide (name, email), progress through tracks and lessons, assessment
                answers and results, and payment records for enrollments you purchase.
            </p>

            <h2>How we use it</h2>
            <p>
                To run your account, track your learning progress, issue certificates, process payments, and
                contact you about your subscription or support requests.
            </p>

            <h2>Sharing</h2>
            <p>
                We do not sell personal data. Information is shared only with service providers needed to
                operate the platform (e.g. payment processors) or when required by law.
            </p>

            <h2>Your choices</h2>
            <p>
                You can request a copy of your data or ask us to delete your account by contacting
                {{ \App\Models\CompanySetting::get('support_email', 'support@englishbaro.test') }}.
            </p>
        </div>
    </div>
</x-layouts.site>
