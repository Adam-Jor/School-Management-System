@php
    $user = auth()->user();
@endphp

<x-card>
    <!-- Livewire Server-Side Filter, Search (debounce 300ms) & Export Bar -->
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center justify-between gap-3 mb-5">
        <div class="grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-2.5 flex-1">
            @if ($isStudent)
                <p class="text-xs font-medium text-secondary">
                    Menampilkan data siswa pribadi yang terhubung dengan akun Anda.
                </p>
            @else
                <div class="relative w-full sm:flex-1 sm:min-w-[220px] sm:max-w-xs">
                    <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
                    <input type="search"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari nama, kode, atau email"
                           aria-label="Cari data siswa"
                           class="w-full h-10 pl-9 pr-3.5 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                </div>

                <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto">
                    <select wire:model.live="classId"
                            aria-label="Filter berdasarkan kelas"
                            class="h-10 px-3 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none w-full sm:w-auto">
                        <option value="">Semua kelas ({{ format_number(count($classes)) }})</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="status"
                            aria-label="Filter berdasarkan status"
                            class="h-10 px-3 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none w-full sm:w-auto">
                        <option value="">Semua status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Pindah">Pindah</option>
                        <option value="Menunggu">Menunggu</option>
                    </select>
                </div>

                @if ($search !== '' || $classId !== '' || $status !== '')
                    <button type="button"
                            wire:click="resetFilters"
                            class="h-10 px-3 rounded-lg border border-line text-xs text-secondary hover:bg-raised transition-colors duration-150 w-full sm:w-auto">
                        Reset
                    </button>
                @endif
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-line">
            <div class="flex items-center gap-2">
                <a href="{{ route('students.index', ['export' => 'xlsx', 'q' => $search, 'class_id' => $classId, 'status' => $status]) }}"
                   class="h-9 px-3 rounded-lg bg-surface border border-line text-xs font-medium text-ink hover:bg-raised inline-flex items-center gap-1.5 transition-colors duration-150">
                    <x-heroicon-o-table-cells class="size-4 stroke-[1.5] text-muted" />
                    <span>Ekspor Excel</span>
                </a>
                <a href="{{ route('students.index', ['export' => 'pdf', 'q' => $search, 'class_id' => $classId, 'status' => $status]) }}"
                   class="h-9 px-3 rounded-lg bg-surface border border-line text-xs font-medium text-ink hover:bg-raised inline-flex items-center gap-1.5 transition-colors duration-150">
                    <x-heroicon-o-document-arrow-down class="size-4 stroke-[1.5] text-muted" />
                    <span>Ekspor PDF</span>
                </a>
            </div>
            <span class="font-mono text-xs text-muted tabular-nums pl-1">
                {{ format_number($students->total()) }} siswa
            </span>
        </div>
    </div>

    <!-- Loading indicator saat pencarian atau filter diproses -->
    <div wire:loading.flex class="hidden items-center gap-3 mb-4 px-3 py-2 rounded-lg bg-sidebar border border-line text-xs text-muted">
        <span class="loading loading-spinner loading-xs text-primary"></span>
        <span>Memuat data siswa...</span>
    </div>

    <!-- Mobile-First Student Cards (< md) -->
    <div class="md:hidden space-y-3" wire:loading.class="opacity-60">
        @forelse ($students as $idx => $student)
            <div wire:key="student-card-{{ $student->id }}" class="rounded-xl border border-line bg-sidebar p-3.5 space-y-3 {{ in_array((string) $student->id, $selected) ? 'ring-2 ring-primary/30 bg-primary-soft/10' : '' }}">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if ($user?->isAdmin())
                            <input type="checkbox" wire:model.live="selected" value="{{ (string) $student->id }}" aria-label="Pilih siswa" class="rounded border-line text-primary focus:ring-primary size-4 shrink-0" />
                        @endif
                        <x-avatar :name="$student->name" :src="$student->img_url" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink truncate">{{ $student->name }}</p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <span class="edudash-keycap">{{ $student->student_code }}</span>
                                <span class="text-xs font-medium text-ink">{{ $student->schoolClass?->name ?? '–' }}</span>
                            </div>
                        </div>
                    </div>
                    <x-status-badge :status="$student->status" />
                </div>

                <div class="pt-2.5 border-t border-line grid grid-cols-1 gap-1 text-xs text-secondary">
                    <div class="flex justify-between gap-2">
                        <span class="text-muted">Email</span>
                        <span class="truncate">{{ $student->user?->email ?? '–' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-muted">Terdaftar</span>
                        <span class="font-mono text-muted tabular-nums">{{ format_date($student->created_at) }}</span>
                    </div>
                </div>

                @if ($user?->isAdmin())
                    <div class="pt-2.5 border-t border-line flex items-center justify-end gap-2">
                        <a href="{{ route('students.edit', $student) }}" wire:navigate
                           class="h-8 px-3 inline-flex items-center rounded-lg bg-surface border border-line text-ink text-xs font-medium hover:bg-raised">
                            Edit
                        </a>
                        <button type="button"
                                @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                                class="h-8 px-3 inline-flex items-center rounded-lg bg-error-soft text-[#B91C1C] text-xs font-medium hover:bg-error hover:text-white">
                            Arsip
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <x-empty-state
                title="Data siswa tidak ditemukan"
                description="Tidak ada siswa yang cocok dengan filter pencarian Anda" />
        @endforelse
    </div>

    <!-- Desktop Table (md+) -->
    <div class="hidden md:block overflow-x-auto" wire:loading.class="opacity-60">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-line text-xs font-normal text-muted">
                    @if ($user?->isAdmin())
                        <th class="py-3 px-3 w-8">
                            <input type="checkbox" wire:model.live="selectAll" aria-label="Pilih semua siswa" class="rounded border-line text-primary focus:ring-primary size-4" />
                        </th>
                    @endif
                    <th class="py-3 pl-2 text-right w-10">No</th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('student_code')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Kode</span>
                            @if ($sortField === 'student_code')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Nama Siswa & Alamat</span>
                            @if ($sortField === 'name')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">Email Akun</th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('class_name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Kelas</span>
                            @if ($sortField === 'class_name')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('created_at')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Terdaftar</span>
                            @if ($sortField === 'created_at')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('status')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Status</span>
                            @if ($sortField === 'status')
                                <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    @if ($user?->isAdmin())
                        <th class="py-3 pr-2 text-right">Tindakan</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-sm">
                @forelse ($students as $idx => $student)
                    <tr wire:key="student-row-{{ $student->id }}" class="group hover:bg-sidebar transition-colors duration-150 {{ in_array((string) $student->id, $selected) ? 'bg-primary-soft/30' : '' }}">
                        @if ($user?->isAdmin())
                            <td class="py-3 px-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ (string) $student->id }}" aria-label="Pilih siswa" class="rounded border-line text-primary focus:ring-primary size-4" />
                            </td>
                        @endif
                        <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                            {{ format_number(($students->currentPage() - 1) * $students->perPage() + $idx + 1) }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="edudash-keycap">{{ $student->student_code }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <x-avatar :name="$student->name" :src="$student->img_url" />
                                <div>
                                    <p class="text-[13px] font-medium text-ink">{{ $student->name }}</p>
                                    <p class="text-xs text-muted truncate max-w-[240px]">
                                        {{ $student->address ?: 'SMA Contoh Nusantara' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-xs text-secondary">
                            {{ $student->user?->email ?? '–' }}
                        </td>
                        <td class="py-3 px-3 text-[13px] text-ink whitespace-nowrap">
                            {{ $student->schoolClass?->name ?? '–' }}
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-muted whitespace-nowrap tabular-nums">
                            {{ format_date($student->created_at) }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            <x-status-badge :status="$student->status" />
                        </td>
                        @if ($user?->isAdmin())
                            <td class="py-3 pr-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('students.edit', $student) }}" wire:navigate
                                       class="h-7 px-2.5 inline-flex items-center rounded-md bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150">
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                                            class="h-7 px-2.5 inline-flex items-center rounded-md bg-error-soft text-[#B91C1C] text-xs font-medium hover:bg-error hover:text-white transition-colors duration-150">
                                        Arsip
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $user?->isAdmin() ? '9' : '8' }}">
                            <x-empty-state
                                title="Data siswa tidak ditemukan"
                                description="Tidak ada siswa yang cocok dengan filter pencarian Anda" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer Pagination -->
    <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
        <span>
            Menampilkan {{ format_number($students->firstItem() ?? 0) }}–{{ format_number($students->lastItem() ?? 0) }}
            dari {{ format_number($students->total()) }} siswa
        </span>
        <div>
            {{ $students->links() }}
        </div>
    </div>

    <!-- Floating Bulk Actions Bar (Admin Only) -->
    @if ($user?->isAdmin() && count($selected) > 0)
        <div class="fixed bottom-20 sm:bottom-6 inset-x-4 sm:inset-x-auto sm:right-6 sm:left-auto bg-ink text-white rounded-2xl p-3.5 shadow-2xl z-40 border border-white/10 flex flex-wrap items-center justify-between gap-3 animate-fade-in max-w-xl">
            <div class="flex items-center gap-2">
                <span class="size-6 rounded-full bg-primary flex items-center justify-center font-mono text-xs font-bold">{{ count($selected) }}</span>
                <span class="text-xs font-medium">siswa dipilih</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Pindah Rombel -->
                <div class="flex items-center gap-1">
                    <select wire:model="bulkTargetClassId" class="h-8 px-2 rounded-lg bg-surface text-ink text-xs border-0">
                        <option value="">Pindah ke rombel...</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="bulkPromote" class="h-8 px-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-medium transition">
                        Pindah
                    </button>
                </div>

                <!-- Ubah Status -->
                <div class="flex items-center gap-1">
                    <select wire:model="bulkNewStatus" class="h-8 px-2 rounded-lg bg-surface text-ink text-xs border-0">
                        <option value="Aktif">Status: Aktif</option>
                        <option value="Pindah">Status: Pindah</option>
                        <option value="Menunggu">Status: Menunggu</option>
                    </select>
                    <button type="button" wire:click="bulkUpdateStatus" class="h-8 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition">
                        Ubah
                    </button>
                </div>

                <!-- Arsip Massal -->
                <button type="button" wire:click="bulkArchive" wire:confirm="Arsipkan seluruh siswa yang dipilih?" class="h-8 px-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium transition">
                    Arsip
                </button>

                <!-- Batal -->
                <button type="button" wire:click="clearSelection" class="text-xs text-white/70 hover:text-white px-1">
                    Batal
                </button>
            </div>
        </div>
    @endif
</x-card>
