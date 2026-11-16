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

<div class="px-5 py-2 first:pl-2 last:pr-2">
    <p class="text-[13px] font-normal text-muted">{{ $label }}</p>
    <p class="text-[32px] lg:text-[34px] font-bold tracking-tight text-ink leading-tight mt-1">
        {{ format_number($value) }}
    </p>
    @if ($delta)
        <p class="text-xs font-medium {{ $deltaColor }} flex items-center gap-1 mt-1.5">
            @if ($deltaType !== 'muted')
                <x-heroicon-o-arrow-trending-up class="size-3.5" />
            @endif
            <span>{{ $delta }}</span>
        </p>
    @endif
</div>
