@props([
    'title',
    'subtitle' => null,
    'isHero' => false,
    'ctaLabel' => null,
    'ctaUrl' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="{{ $isHero ? 'text-3xl lg:text-[40px] leading-tight' : 'text-2xl lg:text-[32px] leading-tight' }} font-bold tracking-tight text-ink">
            {{ $title }}
        </h1>
        @if ($subtitle)
            <p class="text-sm text-muted mt-1.5">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        {{ $actions ?? '' }}

        @if ($ctaLabel && $ctaUrl)
            <a href="{{ $ctaUrl }}" wire:navigate class="edudash-btn-primary inline-flex items-center gap-2 text-sm">
                <x-heroicon-o-plus class="size-4 stroke-[2.5]" />
                <span>{{ $ctaLabel }}</span>
            </a>
        @endif
    </div>
</div>
