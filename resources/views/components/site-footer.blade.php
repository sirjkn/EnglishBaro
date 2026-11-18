@props(['class' => ''])

<footer {{ $attributes->merge(['class' => 'mt-24 bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 text-white '.$class]) }}>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
            <div>
                <h3 class="text-sm font-semibold text-white">EnglishBaro</h3>
                <p class="mt-3 text-sm text-indigo-200">
                    {{ \App\Models\CompanySetting::get('footer_text', 'Learn English online with video lessons, eBooks, and assessments.') }}
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Links</h3>
                <ul class="mt-3 space-y-2 text-sm text-indigo-200">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                    <li><a href="{{ route('home') }}#explore-course" class="hover:text-white">Courses</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Legal</h3>
                <ul class="mt-3 space-y-2 text-sm text-indigo-200">
                    <li><a href="{{ route('privacy') }}" class="hover:text-white">Data Privacy</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white">Terms &amp; Conditions</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Contact</h3>
                <ul class="mt-3 space-y-2 text-sm text-indigo-200">
                    <li>{{ \App\Models\CompanySetting::get('support_email', 'support@englishbaro.test') }}</li>
                    <li>{{ \App\Models\CompanySetting::get('phone', '+000 000 0000') }}</li>
                </ul>
            </div>
        </div>
        <div class="mt-8 flex items-center justify-center border-t border-white/10 pt-6">
            <p class="text-xs text-indigo-300">
                {{ \App\Models\CompanySetting::get('copyright', '© '.date('Y').' EnglishBaro. All rights reserved.') }}
            </p>
        </div>
    </div>
</footer>
