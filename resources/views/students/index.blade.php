<x-layouts.app title="Data Siswa" :breadcrumbs="[['label' => 'Data Siswa', 'url' => route('students.index')], ['label' => 'Daftar Siswa Aktif']]">
    @php
        $user = auth()->user();
    @endphp

    <div x-data="{ importModalOpen: false }">
        <!-- Header Modul (design.md Bagian 5) -->
        <x-page-header
            title="Data Siswa"
            subtitle="Kelola data siswa aktif, relasi kelas (JOIN tbl_classes), impor/ekspor laporan, dan arsip."
            :ctaLabel="$user->isAdmin() ? 'Tambah Siswa' : null"
            :ctaUrl="$user->isAdmin() ? route('students.create') : null">
            <x-slot:actions>
                @if ($user->isAdmin())
                    <button type="button"
                            @click="importModalOpen = true"
                            class="edudash-btn-secondary inline-flex items-center gap-2 text-xs">
                        <x-heroicon-o-arrow-up-tray class="size-4" />
                        <span>Import CSV</span>
                    </button>
                @endif
                <a href="{{ route('students.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="edudash-btn-secondary inline-flex items-center gap-2 text-xs">
                    <x-heroicon-o-arrow-down-tray class="size-4" />
                    <span>Ekspor CSV</span>
                </a>
                <a href="{{ route('students.index', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="edudash-btn-secondary inline-flex items-center gap-2 text-xs">
                    <x-heroicon-o-document-arrow-down class="size-4" />
                    <span>Ekspor PDF</span>
                </a>
                @if ($user->isAdmin())
                    <a href="{{ route('archive.index', ['tab' => 'students']) }}" wire:navigate
                       class="px-4 py-2.5 rounded-full bg-primary-soft text-primary font-semibold text-xs inline-flex items-center gap-1.5 hover:bg-primary hover:text-white transition">
                        <x-heroicon-o-archive-box class="size-4" />
                        <span>Archive ({{ format_number($archivedCount) }})</span>
                    </a>
                @endif
            </x-slot:actions>
        </x-page-header>

        <!-- Kartu Tabel Utama -->
        <x-card>
            <!-- Filter & Search Bar -->
            <form action="{{ route('students.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="relative flex-1 min-w-[240px] max-w-sm">
                        <x-heroicon-o-magnifying-glass class="size-4 text-muted absolute left-4 top-3 pointer-events-none" />
                        <input type="search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Cari kode STD, nama siswa, atau email…"
                               aria-label="Cari data siswa"
                               class="w-full h-10 pl-10 pr-4 rounded-full bg-sidebar border border-primary/50 text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                    </div>

                    <select name="class_id"
                            onchange="this.form.submit()"
                            aria-label="Filter berdasarkan kelas"
                            class="h-10 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-ink focus:border-primary focus:outline-none">
                        <option value="">Semua Kelas ({{ format_number(count($classes)) }})</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}" @selected((string) $classId === (string) $cls->id)>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status"
                            onchange="this.form.submit()"
                            aria-label="Filter berdasarkan status"
                            class="h-10 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-ink focus:border-primary focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="Aktif" @selected($status === 'Aktif')>Aktif</option>
                        <option value="Pindah" @selected($status === 'Pindah')>Pindah</option>
                        <option value="Menunggu" @selected($status === 'Menunggu')>Menunggu</option>
                    </select>

                    <select name="sort"
                            onchange="this.form.submit()"
                            aria-label="Urutkan data siswa"
                            class="h-10 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-muted focus:border-primary focus:outline-none">
                        <option value="latest" @selected($sort === 'latest')>Urutkan: Terbaru</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Urutkan: Nama A–Z</option>
                        <option value="code_asc" @selected($sort === 'code_asc')>Urutkan: Kode Siswa</option>
                    </select>
                </div>

                <span class="px-4 py-1.5 rounded-full bg-primary-soft text-primary text-xs font-semibold">
                    Total: {{ format_number($students->total()) }} Siswa
                </span>
            </form>

            <!-- Tabel Data Siswa -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-medium text-muted">
                            <th class="py-3 pl-2 text-right w-12">No.</th>
                            <th class="py-3 px-3">ID Siswa</th>
                            <th class="py-3 px-3">Nama Siswa & Alamat</th>
                            <th class="py-3 px-3">Akun User (tbl_users)</th>
                            <th class="py-3 px-3">Kelas (JOIN)</th>
                            <th class="py-3 px-3">Tgl Terdaftar</th>
                            <th class="py-3 px-3">Status</th>
                            @if ($user->isAdmin())
                                <th class="py-3 pr-2 text-right">Aksi CRUD</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($students as $idx => $student)
                            <tr class="group hover:bg-page/60 transition duration-150">
                                <td class="py-3.5 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                    {{ format_number(($students->currentPage() - 1) * $students->perPage() + $idx + 1) }}
                                </td>
                                <td class="py-3.5 px-3 text-xs font-medium text-muted whitespace-nowrap">
                                    {{ $student->student_code }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$student->name" :src="$student->img_url" />
                                        <div>
                                            <p class="text-[13px] font-semibold text-ink">{{ $student->name }}</p>
                                            <p class="text-[11px] text-muted truncate max-w-[240px]">
                                                {{ $student->address ?: 'SMA Contoh Nusantara' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-xs text-muted">
                                    {{ $student->user?->email ?? '–' }}
                                </td>
                                <td class="py-3.5 px-3 text-[13px] font-medium text-ink whitespace-nowrap">
                                    {{ $student->schoolClass?->name ?? '–' }}
                                </td>
                                <td class="py-3.5 px-3 text-xs text-muted whitespace-nowrap">
                                    {{ format_date($student->created_at) }}
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <x-status-badge :status="$student->status" />
                                </td>
                                @if ($user->isAdmin())
                                    <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('students.edit', $student) }}" wire:navigate
                                               class="px-3 py-1 rounded-full bg-primary-soft text-primary text-[11px] font-semibold hover:bg-primary hover:text-white transition">
                                                Edit
                                            </a>
                                            <button type="button"
                                                    @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                                                    class="px-3 py-1 rounded-full bg-error-soft text-[#B91C1C] text-[11px] font-semibold hover:bg-error hover:text-white transition">
                                                Archive
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state
                                        title="Data Siswa Tidak Ditemukan"
                                        description="Tidak ada siswa yang cocok dengan filter pencarian Anda."
                                        actionLabel="Reset Pencarian"
                                        :actionUrl="route('students.index')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted">
                <span>
                    Menampilkan {{ format_number($students->firstItem() ?? 0) }}–{{ format_number($students->lastItem() ?? 0) }}
                    dari {{ format_number($students->total()) }} siswa · Server-side Pagination
                </span>
                <div>
                    {{ $students->links() }}
                </div>
            </div>
        </x-card>

        <!-- Modal Import CSV Siswa -->
        @if ($user->isAdmin())
            <div x-show="importModalOpen"
                 x-transition.opacity
                 style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 backdrop-blur-xs p-4">
                <div @click.outside="importModalOpen = false"
                     class="bg-surface rounded-[20px] p-6 max-w-md w-full shadow-2xl border border-line">
                    <h3 class="text-lg font-bold text-ink">Import Data Siswa (CSV)</h3>
                    <p class="text-xs text-muted mt-1 leading-relaxed">
                        Unggah file CSV dengan kolom: <code class="text-ink">student_code, name, status</code>.
                    </p>
                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="file-input file-input-bordered w-full rounded-full text-xs" />
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="importModalOpen = false" class="edudash-btn-secondary btn-sm text-xs">Batal</button>
                            <button type="submit" class="edudash-btn-primary btn-sm text-xs">Mulai Import</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
