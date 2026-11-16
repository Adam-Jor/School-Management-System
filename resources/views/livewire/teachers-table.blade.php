@php
    $user = auth()->user();
@endphp

<x-card>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-[220px] max-w-xs">
                <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
                <input type="search"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama guru, telepon, atau mapel"
                       aria-label="Cari data guru"
                       class="w-full h-10 pl-9 pr-3.5 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
            </div>

            <select wire:model.live="subjectId"
                    aria-label="Filter mata pelajaran"
                    class="h-10 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                <option value="">Semua mapel ({{ format_number(count($subjects)) }})</option>
                @foreach ($subjects as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->code }} · {{ $sub->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="status"
                    aria-label="Filter status guru"
                    class="h-10 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                <option value="">Semua status</option>
                <option value="Aktif">Aktif</option>
                <option value="Cuti">Cuti</option>
                <option value="Pindah">Pindah</option>
            </select>

            @if ($search !== '' || $subjectId !== '' || $status !== '')
                <button type="button"
                        wire:click="resetFilters"
                        class="h-10 px-3 rounded-lg border border-line text-xs text-secondary hover:bg-raised transition-colors duration-150">
                    Reset
                </button>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('teachers.index', ['export' => 'xlsx', 'q' => $search, 'subject_id' => $subjectId, 'status' => $status]) }}"
               class="h-9 px-3 rounded-lg bg-surface border border-line text-xs font-medium text-ink hover:bg-raised inline-flex items-center gap-1.5 transition-colors duration-150">
                <x-heroicon-o-table-cells class="size-4 stroke-[1.5] text-muted" />
                <span>Ekspor Excel</span>
            </a>
            <a href="{{ route('teachers.index', ['export' => 'pdf', 'q' => $search, 'subject_id' => $subjectId, 'status' => $status]) }}"
               class="h-9 px-3 rounded-lg bg-surface border border-line text-xs font-medium text-ink hover:bg-raised inline-flex items-center gap-1.5 transition-colors duration-150">
                <x-heroicon-o-document-arrow-down class="size-4 stroke-[1.5] text-muted" />
                <span>Ekspor PDF</span>
            </a>
            <span class="font-mono text-xs text-muted tabular-nums pl-1">
                {{ format_number($teachers->total()) }} guru
            </span>
        </div>
    </div>

    <div wire:loading.flex class="hidden items-center gap-3 mb-4 px-3 py-2 rounded-lg bg-sidebar border border-line text-xs text-muted">
        <span class="loading loading-spinner loading-xs text-primary"></span>
        <span>Memuat data guru...</span>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-60">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-line text-xs font-normal text-muted">
                    <th class="py-3 pl-2 text-right w-10">No</th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('teacher_code')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Kode</span>
                            @if ($sortField === 'teacher_code')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Nama Guru & Akun</span>
                            @if ($sortField === 'name')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('subject_name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Mata Pelajaran</span>
                            @if ($sortField === 'subject_name')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">No. Telepon</th>
                    <th class="py-3 px-3">Terdaftar</th>
                    <th class="py-3 px-3">Status</th>
                    @if ($user?->isAdmin())
                        <th class="py-3 pr-2 text-right">Tindakan</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-sm">
                @forelse ($teachers as $idx => $teacher)
                    <tr wire:key="teacher-row-{{ $teacher->id }}" class="group hover:bg-sidebar transition-colors duration-150">
                        <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                            {{ format_number(($teachers->currentPage() - 1) * $teachers->perPage() + $idx + 1) }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="edudash-keycap">{{ $teacher->teacher_code }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <x-avatar :name="$teacher->name" :src="$teacher->img_url" />
                                <div>
                                    <p class="text-[13px] font-medium text-ink">{{ $teacher->name }}</p>
                                    <p class="text-xs text-muted truncate max-w-[260px]">
                                        {{ $teacher->user?->email ?? '–' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            @if ($teacher->subject)
                                <span class="edudash-keycap">{{ $teacher->subject->code }} · {{ $teacher->subject->name }}</span>
                            @else
                                <span class="text-xs text-muted">–</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-secondary whitespace-nowrap tabular-nums">
                            {{ $teacher->phone ?? '–' }}
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-muted whitespace-nowrap tabular-nums">
                            {{ format_date($teacher->created_at) }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            <x-status-badge :status="$teacher->status" />
                        </td>
                        @if ($user?->isAdmin())
                            <td class="py-3 pr-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('teachers.edit', $teacher) }}" wire:navigate
                                       class="h-7 px-2.5 inline-flex items-center rounded-md bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150">
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="openArchiveModal('{{ route('teachers.archive', $teacher) }}', '{{ addslashes($teacher->name) }}', '{{ $teacher->teacher_code }}')"
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
                            <x-empty-state
                                title="Data guru tidak ditemukan"
                                description="Tidak ada guru yang cocok dengan kata kunci atau filter mata pelajaran" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
        <span>
            Menampilkan {{ format_number($teachers->firstItem() ?? 0) }}–{{ format_number($teachers->lastItem() ?? 0) }}
            dari {{ format_number($teachers->total()) }} guru
        </span>
        <div>
            {{ $teachers->links() }}
        </div>
    </div>
</x-card>
