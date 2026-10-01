<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? 'EnglishBaro is an online English learning platform with video lessons, eBooks, and assessments.' }}">

        <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
        <link rel="icon" href="{{ asset('images/favicon-16x16.png') }}" sizes="16x16" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white dark:bg-gray-900 dark:text-gray-100 pb-16 md:pb-0">
        <x-site-header />

        @if (session('status'))
            <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 text-sm text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-24 bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div>
                        <div class="text-lg font-bold text-white">EnglishBaro</div>
                        <p class="mt-2 text-sm text-indigo-200">
                            {{ \App\Models\CompanySetting::get('footer_text', 'Learn English online with video lessons, eBooks, and assessments.') }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">Links</h3>
                        <ul class="mt-3 space-y-2 text-sm text-indigo-200">
                            <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                            <li><a href="{{ route('tracks.index') }}" class="hover:text-white">Tracks</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
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
                <div class="mt-8 flex flex-col-reverse items-center gap-6 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-indigo-300">
                        {{ \App\Models\CompanySetting::get('copyright', '© '.date('Y').' EnglishBaro. All rights reserved.') }}
                    </p>

                    <div class="flex items-center gap-3">
                        @php
                            $socialLinks = [
                                'facebook' => \App\Models\CompanySetting::get('facebook_url', '#'),
                                'linkedin' => \App\Models\CompanySetting::get('linkedin_url', '#'),
                                'twitter' => \App\Models\CompanySetting::get('twitter_url', '#'),
                                'youtube' => \App\Models\CompanySetting::get('youtube_url', '#'),
                                'instagram' => \App\Models\CompanySetting::get('instagram_url', '#'),
                                'tiktok' => \App\Models\CompanySetting::get('tiktok_url', '#'),
                            ];
                        @endphp

                        @foreach ($socialLinks as $network => $url)
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ ucfirst($network) }}"
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-indigo-900 transition hover:-translate-y-0.5 hover:bg-indigo-100 hover:shadow-lg"
                            >
                                @switch($network)
                                    @case('facebook')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.84c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.45 2.91h-2.33V22c4.78-.79 8.44-4.94 8.44-9.94z"/></svg>
                                        @break
                                    @case('linkedin')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3V9zm7 0h3.6v1.64h.05c.5-.95 1.73-1.95 3.56-1.95 3.81 0 4.51 2.51 4.51 5.78V21h-4v-5.5c0-1.31-.02-3-1.83-3-1.83 0-2.11 1.43-2.11 2.9V21h-4V9z"/></svg>
                                        @break
                                    @case('twitter')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M18.9 2H22l-7.6 8.7L23.3 22h-7.1l-5.6-6.8L4.1 22H1l8.1-9.3L.9 2h7.3l5.1 6.3L18.9 2zm-1.2 18h1.9L7.4 4H5.4l12.3 16z"/></svg>
                                        @break
                                    @case('youtube')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M23.5 7.16s-.23-1.64-.94-2.36c-.9-.95-1.9-.95-2.36-1.01C16.9 3.5 12 3.5 12 3.5h-.01s-4.9 0-8.2.29c-.46.06-1.46.06-2.36 1.01-.71.72-.94 2.36-.94 2.36S.26 9.09.26 11.02v1.8c0 1.93.23 3.86.23 3.86s.23 1.64.94 2.36c.9.95 2.07.92 2.6 1.02 1.88.18 8 .24 8 .24s4.9-.01 8.2-.3c.46-.06 1.46-.06 2.36-1.01.71-.72.94-2.36.94-2.36s.23-1.93.23-3.86v-1.8c0-1.93-.23-3.86-.23-3.86zM9.74 14.85V8.66l6.27 3.1-6.27 3.09z"/></svg>
                                        @break
                                    @case('instagram')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.21.6 1.76 1.15.55.55.9 1.1 1.15 1.76.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.15 1.76 4.9 4.9 0 0 1-1.76 1.15c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.76-1.15 4.9 4.9 0 0 1-1.15-1.76c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.26-.66.6-1.21 1.15-1.76a4.9 4.9 0 0 1 1.76-1.15c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.28 2 12 2zm0 1.8c-2.67 0-2.99.01-4.04.06-.87.04-1.34.18-1.65.3-.42.16-.71.35-1.02.66-.31.31-.5.6-.66 1.02-.12.31-.26.78-.3 1.65-.05 1.05-.06 1.37-.06 4.04s.01 2.99.06 4.04c.04.87.18 1.34.3 1.65.16.42.35.71.66 1.02.31.31.6.5 1.02.66.31.12.78.26 1.65.3 1.05.05 1.37.06 4.04.06s2.99-.01 4.04-.06c.87-.04 1.34-.18 1.65-.3.42-.16.71-.35 1.02-.66.31-.31.5-.6.66-1.02.12-.31.26-.78.3-1.65.05-1.05.06-1.37.06-4.04s-.01-2.99-.06-4.04c-.04-.87-.18-1.34-.3-1.65a2.76 2.76 0 0 0-.66-1.02 2.76 2.76 0 0 0-1.02-.66c-.31-.12-.78-.26-1.65-.3-1.05-.05-1.37-.06-4.04-.06zm0 3.25a4.95 4.95 0 1 1 0 9.9 4.95 4.95 0 0 1 0-9.9zm0 1.8a3.15 3.15 0 1 0 0 6.3 3.15 3.15 0 0 0 0-6.3zm5.15-1.99a1.16 1.16 0 1 1-2.32 0 1.16 1.16 0 0 1 2.32 0z"/></svg>
                                        @break
                                    @case('tiktok')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current"><path d="M16.6 2h-3.2v13.6a2.8 2.8 0 1 1-2-2.69V9.6a6.1 6.1 0 1 0 5.2 6.03V8.93a7.3 7.3 0 0 0 4.4 1.47V7.2a4.3 4.3 0 0 1-4.4-5.2z"/></svg>
                                        @break
                                @endswitch
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
