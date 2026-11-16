@props([
    'status' => 'Aktif',
])
@php
    $normalized = ucfirst(strtolower(trim($status)));
    [$bgClass, $textClass, $dotClass] = match ($normalized) {
        'Aktif', 'Hadir' => ['bg-success-soft', 'text-[#15803D]', 'bg-success'],
        'Pindah', 'Nonaktif', 'Alpa' => ['bg-error-soft', 'text-[#B91C1C]', 'bg-error'],
        'Menunggu', 'Cuti', 'Izin', 'Sakit' => ['bg-warning-soft', 'text-[#B45309]', 'bg-warning'],
        default => ['bg-raised', 'text-secondary', 'bg-muted'],
    };
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {{ $bgClass }} {{ $textClass }}">
    <span class="size-1.5 rounded-full {{ $dotClass }}"></span>
    <span>{{ $normalized }}</span>
</span>
