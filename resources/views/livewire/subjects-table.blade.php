@php $user = auth()->user(); @endphp

<x-card>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="relative flex-1 min-w-[240px] max-w-sm">
            <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
            <input type="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari kode atau nama mata pelajaran"
                   aria-label="Cari mata pelajaran"
                   class="w-full h-10 pl-9 pr-3.5 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
        </div>
        <span class="font-mono text-xs text-muted tabular-nums">
            {{ format_number($subjects->total()) }} mata pelajaran
        </span>
    </div>

    <div wire:loading.flex class="hidden items-center gap-3 mb-4 px-3 py-2 rounded-lg bg-sidebar border border-line text-xs text-muted">
        <span class="loading loading-spinner loading-xs text-primary"></span>
        <span>Memuat mata pelajaran...</span>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-60">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-line text-xs font-normal text-muted">
                    <th class="py-3 pl-2 text-right w-10">No</th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('code')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Kode</span>
                            @if ($sortField === 'code') <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Mata Pelajaran</span>
                            @if ($sortField === 'name') <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">Silabus</th>
                    <th class="py-3 px-3 text-right">Beban JP</th>
                    <th class="py-3 px-3 text-right">Pengajar</th>
                    <th class="py-3 px-3">Dibuat</th>
                    @if ($user?->isAdmin())
                        <th class="py-3 pr-2 text-right">Tindakan</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-sm">
                @forelse ($subjects as $idx => $sub)
                    <tr wire:key="subject-row-{{ $sub->id }}" class="group hover:bg-sidebar transition-colors duration-150">
                        <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                            {{ format_number(($subjects->currentPage() - 1) * $subjects->perPage() + $idx + 1) }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="edudash-keycap">{{ $sub->code }}</span>
                        </td>
                        <td class="py-3 px-3 text-[13px] font-medium text-ink">
                            {{ $sub->name }}
                        </td>
                        <td class="py-3 px-3 text-xs text-secondary max-w-md">
                            {{ $sub->description ?: '–' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-xs text-ink tabular-nums">
                            {{ format_number($sub->credit_hours) }} JP
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-xs font-medium text-primary tabular-nums">
                            {{ format_number($sub->teachers_count) }}
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-muted whitespace-nowrap tabular-nums">
                            {{ format_date($sub->created_at) }}
                        </td>
                        @if ($user?->isAdmin())
                            <td class="py-3 pr-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('subjects.edit', $sub) }}" wire:navigate
                                       class="h-7 px-2.5 inline-flex items-center rounded-md bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150">
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="openArchiveModal('{{ route('subjects.archive', $sub) }}', '{{ addslashes($sub->name) }}', '{{ $sub->code }}')"
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
                            <x-empty-state title="Data mata pelajaran tidak ditemukan" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
        <span>Menampilkan {{ format_number($subjects->firstItem() ?? 0) }}–{{ format_number($subjects->lastItem() ?? 0) }} dari {{ format_number($subjects->total()) }} mata pelajaran</span>
        <div>{{ $subjects->links() }}</div>
    </div>
</x-card>
