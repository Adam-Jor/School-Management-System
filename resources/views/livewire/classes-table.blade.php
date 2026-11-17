@php $user = auth()->user(); @endphp

<x-card>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative w-full sm:flex-1 sm:min-w-[240px] sm:max-w-sm">
            <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
            <input type="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari kode atau nama kelas"
                   aria-label="Cari kelas"
                   class="w-full h-10 pl-9 pr-3.5 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
        </div>
        <span class="font-mono text-xs text-muted tabular-nums">
            {{ format_number($classes->total()) }} kelas
        </span>
    </div>

    <div wire:loading.flex class="hidden items-center gap-3 mb-4 px-3 py-2 rounded-lg bg-sidebar border border-line text-xs text-muted">
        <span class="loading loading-spinner loading-xs text-primary"></span>
        <span>Memuat data kelas...</span>
    </div>

    <!-- Mobile-First Class Cards (< md) -->
    <div class="md:hidden space-y-2.5" wire:loading.class="opacity-60">
        @forelse ($classes as $idx => $cls)
            <div wire:key="class-card-{{ $cls->id }}" class="rounded-xl border border-line bg-sidebar p-3.5 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="edudash-keycap">{{ $cls->code }}</span>
                        <h3 class="text-sm font-semibold text-ink">{{ $cls->name }}</h3>
                    </div>
                    <span class="font-mono text-xs font-medium text-primary tabular-nums">
                        {{ format_number($cls->students_count) }}/{{ format_number($cls->capacity) }} siswa
                    </span>
                </div>
                <p class="text-xs text-secondary">{{ $cls->description ?: 'Rombongan belajar aktif' }}</p>
                @if ($user?->isAdmin())
                    <div class="pt-2 border-t border-line flex items-center justify-end gap-2">
                        <a href="{{ route('classes.edit', $cls) }}" wire:navigate
                           class="h-8 px-3 inline-flex items-center rounded-lg bg-surface border border-line text-ink text-xs font-medium">
                            Edit
                        </a>
                        <button type="button"
                                @click="openArchiveModal('{{ route('classes.archive', $cls) }}', '{{ addslashes($cls->name) }}', '{{ $cls->code }}')"
                                class="h-8 px-3 inline-flex items-center rounded-lg bg-error-soft text-[#B91C1C] text-xs font-medium">
                            Arsip
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <x-empty-state title="Data kelas tidak ditemukan" />
        @endforelse
    </div>

    <!-- Desktop Table (md+) -->
    <div class="hidden md:block overflow-x-auto" wire:loading.class="opacity-60">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-line text-xs font-normal text-muted">
                    <th class="py-3 pl-2 text-right w-10">No</th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('code')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Kode Kelas</span>
                            @if ($sortField === 'code') <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Nama Kelas</span>
                            @if ($sortField === 'name') <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">Deskripsi Ruang</th>
                    <th class="py-3 px-3 text-right">Kapasitas</th>
                    <th class="py-3 px-3 text-right">Siswa</th>
                    <th class="py-3 px-3">Dibuat</th>
                    @if ($user?->isAdmin())
                        <th class="py-3 pr-2 text-right">Tindakan</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-sm">
                @forelse ($classes as $idx => $cls)
                    <tr wire:key="class-row-{{ $cls->id }}" class="group hover:bg-sidebar transition-colors duration-150">
                        <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                            {{ format_number(($classes->currentPage() - 1) * $classes->perPage() + $idx + 1) }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="edudash-keycap">{{ $cls->code }}</span>
                        </td>
                        <td class="py-3 px-3 text-[13px] font-medium text-ink">
                            {{ $cls->name }}
                        </td>
                        <td class="py-3 px-3 text-xs text-secondary max-w-md">
                            {{ $cls->description ?: '–' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-xs text-ink tabular-nums">
                            {{ format_number($cls->capacity) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-xs font-medium text-primary tabular-nums">
                            {{ format_number($cls->students_count) }}
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-muted whitespace-nowrap tabular-nums">
                            {{ format_date($cls->created_at) }}
                        </td>
                        @if ($user?->isAdmin())
                            <td class="py-3 pr-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('classes.edit', $cls) }}" wire:navigate
                                       class="h-7 px-2.5 inline-flex items-center rounded-md bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150">
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="openArchiveModal('{{ route('classes.archive', $cls) }}', '{{ addslashes($cls->name) }}', '{{ $cls->code }}')"
                                            class="h-7 px-2.5 inline-flex items-center rounded-md bg-error-soft text-[#B91C1C] text-xs font-medium hover:bg-error hover:text-white transition-colors duration-150">
                                        Arsip
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state title="Data kelas tidak ditemukan" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
        <span>Menampilkan {{ format_number($classes->firstItem() ?? 0) }}–{{ format_number($classes->lastItem() ?? 0) }} dari {{ format_number($classes->total()) }} kelas</span>
        <div>{{ $classes->links() }}</div>
    </div>
</x-card>
