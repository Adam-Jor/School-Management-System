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

<div class="px-5 py-1 first:pl-1 last:pr-1">
    <p class="text-xs font-normal text-muted">{{ $label }}</p>
    <p class="text-[28px] lg:text-[32px] font-medium text-ink leading-none mt-2 tabular-nums">
        {{ format_number($value) }}
    </p>
    @if ($delta)
        <p class="text-xs font-normal {{ $deltaColor }} mt-2 tabular-nums">
            {{ $delta }}
        </p>
    @endif
</div>
