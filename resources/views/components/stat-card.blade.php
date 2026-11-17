@props([
    'label',
    'value' => 0,
    'delta' => null,
    'deltaType' => 'success',
])
@php
    $deltaColor = match($deltaType) {
        'success' => 'text-success',
        'primary' => 'text-primary',
        default => 'text-muted',
    };
@endphp

<div class="px-2 sm:px-4 lg:px-5 py-1.5 sm:py-1 lg:first:pl-1 lg:last:pr-1">
    <p class="text-[11px] sm:text-xs font-normal text-muted truncate">{{ $label }}</p>
    <p class="text-2xl sm:text-[28px] lg:text-[32px] font-medium text-ink leading-none mt-1.5 sm:mt-2 tabular-nums">
        {{ format_number($value) }}
    </p>
    @if ($delta)
        <p class="text-[11px] sm:text-xs font-normal {{ $deltaColor }} mt-1.5 sm:mt-2 tabular-nums line-clamp-1">
            {{ $delta }}
        </p>
    @endif
</div>
