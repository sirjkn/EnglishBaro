@props(['status'])

@php
    $color = match ($status) {
        'successful' => 'green',
        'pending', 'processing' => 'yellow',
        'failed', 'cancelled' => 'red',
        'refunded' => 'indigo',
        default => 'gray',
    };
@endphp

<x-badge :color="$color" dot>{{ ucfirst($status) }}</x-badge>
