@props([
    'title' => 'Belum ada data ditemukan',
    'description' => 'Data yang Anda cari belum tersedia atau tidak cocok dengan filter pencarian',
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="py-12 px-4 text-center max-w-md mx-auto">
    <div class="size-10 rounded-lg bg-raised border border-line text-muted flex items-center justify-center mx-auto mb-3">
        <x-heroicon-o-magnifying-glass class="size-[18px] stroke-[1.5]" />
    </div>
    <h3 class="text-sm font-medium text-ink">{{ rtrim($title, '.') }}</h3>
    <p class="text-xs text-muted mt-1 leading-relaxed">{{ $description }}</p>

    @if ($actionLabel && $actionUrl)
        <div class="mt-4">
            <a href="{{ $actionUrl }}" wire:navigate class="edudash-btn-secondary h-8 px-3 text-xs">
                <span>{{ $actionLabel }}</span>
            </a>
        </div>
    @endif
</div>
