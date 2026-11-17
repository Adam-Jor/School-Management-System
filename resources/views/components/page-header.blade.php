@props([
    'title',
    'subtitle' => null,
    'isHero' => false,
    'ctaLabel' => null,
    'ctaUrl' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="min-w-0">
        <h1 class="{{ $isHero ? 'text-xl sm:text-[26px] lg:text-[32px] leading-[1.2] tracking-[-0.02em]' : 'text-lg sm:text-2xl lg:text-[28px] leading-[1.2] tracking-[-0.01em]' }} font-medium text-ink">
            {{ rtrim($title, '.') }}
        </h1>
        @if ($subtitle)
            <p class="text-xs sm:text-[13px] lg:text-sm text-secondary mt-1 leading-relaxed">{{ rtrim($subtitle, '.') }}</p>
        @endif
    </div>

    @if (isset($actions) || ($ctaLabel && $ctaUrl))
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto shrink-0">
            {{ $actions ?? '' }}

            @if ($ctaLabel && $ctaUrl)
                <a href="{{ $ctaUrl }}" wire:navigate class="edudash-btn-primary gap-2 w-full sm:w-auto justify-center">
                    <x-heroicon-o-plus class="size-4 stroke-[1.5]" />
                    <span>{{ $ctaLabel }}</span>
                </a>
            @endif
        </div>
    @endif
</div>
