@props([
    'title',
    'subtitle' => null,
    'isHero' => false,
    'ctaLabel' => null,
    'ctaUrl' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="{{ $isHero ? 'text-[28px] lg:text-[32px] leading-[1.15] tracking-[-0.02em]' : 'text-2xl lg:text-[28px] leading-[1.15] tracking-[-0.01em]' }} font-medium text-ink">
            {{ rtrim($title, '.') }}
        </h1>
        @if ($subtitle)
            <p class="text-[13px] lg:text-sm text-secondary mt-1">{{ rtrim($subtitle, '.') }}</p>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-2">
        {{ $actions ?? '' }}

        @if ($ctaLabel && $ctaUrl)
            <a href="{{ $ctaUrl }}" wire:navigate class="edudash-btn-primary gap-2">
                <x-heroicon-o-plus class="size-4 stroke-[1.5]" />
                <span>{{ $ctaLabel }}</span>
            </a>
        @endif
    </div>
</div>
