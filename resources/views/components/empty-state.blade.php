@props([
    'title' => 'Belum ada data ditemukan',
    'description' => 'Data yang Anda cari belum tersedia atau tidak cocok dengan filter pencarian.',
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="py-12 px-4 text-center max-w-md mx-auto">
    <div class="size-14 rounded-full bg-primary-soft text-primary flex items-center justify-center mx-auto mb-3">
        <x-heroicon-o-magnifying-glass class="size-6" />
    </div>
    <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
    <p class="text-xs text-muted mt-1 leading-relaxed">{{ $description }}</p>

    @if ($actionLabel && $actionUrl)
        <div class="mt-4">
            <a href="{{ $actionUrl }}" wire:navigate class="edudash-btn-primary btn-sm inline-flex items-center gap-1.5 text-xs">
                <span>{{ $actionLabel }}</span>
            </a>
        </div>
    @endif
</div>
