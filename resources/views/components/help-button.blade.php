@props(['class' => 'flex items-center gap-1.5 rounded-md bg-green-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-green-600', 'labelClass' => ''])

@php
    $helpPhone = preg_replace('/\D+/', '', \App\Models\CompanySetting::get('phone', ''));
    $helpHref = $helpPhone ? "https://wa.me/{$helpPhone}" : route('contact');
@endphp

<a href="{{ $helpHref }}" target="_blank" rel="noopener noreferrer" {{ $attributes->merge(['class' => $class]) }}>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 sm:h-5 sm:w-5 fill-current"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.05-1.32A10 10 0 1 0 12 2zm0 18.2a8.17 8.17 0 0 1-4.17-1.14l-.3-.18-3.1.81.83-3.02-.2-.31A8.2 8.2 0 1 1 12 20.2zm4.52-6.12c-.25-.12-1.47-.72-1.7-.8-.23-.08-.39-.12-.56.12-.17.25-.64.8-.78.96-.14.17-.29.19-.54.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.44.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.44-.06-.12-.56-1.35-.77-1.85-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.87.85-.87 2.08s.89 2.42 1.02 2.59c.12.17 1.75 2.67 4.24 3.74.59.26 1.05.41 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.16-.48-.28z"/></svg>
    <span class="{{ $labelClass }}">WhatsApp</span>
</a>
