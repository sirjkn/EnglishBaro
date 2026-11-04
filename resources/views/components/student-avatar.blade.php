@props(['profile' => null, 'size' => 'h-20 w-20'])

@php
    $imageUrl = $profile?->profileImage?->resolved_url;
    $gender = $profile?->gender;
@endphp

<span {{ $attributes->merge(['class' => "flex {$size} shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700"]) }}>
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="Profile photo" class="h-full w-full object-cover">
    @elseif ($gender === 'female')
        <svg viewBox="0 0 100 100" class="h-full w-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="50" cy="50" r="50" fill="#EEF2F8" />
            <path d="M50 20c-13 0-22 11-22 24 0 4 .5 8 1.5 11.5C32 48 40 44 50 44s18 4 20.5 11.5C71.5 52 72 48 72 44c0-13-9-24-22-24Z" fill="#D7DEEA" />
            <circle cx="50" cy="46" r="15" fill="#C9D2E2" />
            <path d="M22 95c2-18 13-31 28-31s26 13 28 31" fill="#D7DEEA" />
        </svg>
    @else
        <svg viewBox="0 0 100 100" class="h-full w-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="50" cy="50" r="50" fill="#EEF2F8" />
            <circle cx="50" cy="42" r="15" fill="#C9D2E2" />
            <path d="M22 95c2-18 13-31 28-31s26 13 28 31" fill="#D7DEEA" />
        </svg>
    @endif
</span>
