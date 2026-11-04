{{--
    Original doodle-pattern background for support ticket threads — inspired
    by the "chat wallpaper" look (dark base + scattered colorful outline
    icons), but drawn from scratch as our own SVG tile rather than reusing
    any third party's actual artwork.
--}}
@php
    $tile = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220" viewBox="0 0 220 220">
  <rect width="220" height="220" fill="#0b1324"/>
  <g fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M20 30h24a6 6 0 0 1 6 6v14a6 6 0 0 1-6 6H34l-8 8v-8h-6a6 6 0 0 1-6-6V36a6 6 0 0 1 6-6Z" stroke="#34d399"/>
    <circle cx="150" cy="24" r="10" stroke="#60a5fa"/>
    <path d="M150 14v20M140 24h20" stroke="#60a5fa"/>
    <path d="M190 60c4-6 10-6 14 0M188 66c6-8 16-8 22 0" stroke="#a78bfa"/>
    <path d="M24 90l8 8 16-16" stroke="#34d399"/>
    <path d="M70 70l5 12 12 5-12 5-5 12-5-12-12-5 12-5Z" stroke="#f472b6"/>
    <path d="M120 100h20M120 108h14M120 116h20" stroke="#60a5fa"/>
    <circle cx="190" cy="120" r="9" stroke="#fbbf24"/>
    <path d="M30 140h20v14H30zM36 140v-6a4 4 0 0 1 8 0v6" stroke="#f472b6"/>
    <path d="M100 150c0-8 8-12 14-8 4-8 16-6 16 4 0 8-10 14-16 18-6-4-14-8-14-14Z" stroke="#fb7185"/>
    <path d="M150 170l6 6 12-12" stroke="#34d399"/>
    <path d="M10 180c3-5 8-5 11 0M8 186c5-7 13-7 18 0" stroke="#a78bfa"/>
    <circle cx="80" cy="190" r="8" stroke="#60a5fa"/>
    <path d="M190 190h18M190 196h12M190 202h18" stroke="#fbbf24"/>
  </g>
</svg>
SVG;

    $dataUri = 'data:image/svg+xml;base64,'.base64_encode($tile);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl']) }} style="background-color:#0b1324; background-image:url('{{ $dataUri }}'); background-repeat:repeat;">
    {{ $slot }}
</div>
