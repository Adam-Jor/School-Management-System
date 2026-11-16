<x-layouts.app title="Data Siswa" :breadcrumbs="[['label' => 'Data Siswa', 'url' => route('students.index')], ['label' => 'Direktori Siswa']]">
    @php
        $user = auth()->user();
    @endphp

    <div x-data="{ importModalOpen: false }">
        <x-page-header
            title="Data Siswa"
            subtitle="Direktori siswa aktif, penempatan kelas, dan status registrasi"
            :ctaLabel="$user->isAdmin() ? 'Tambah Siswa' : null"
            :ctaUrl="$user->isAdmin() ? route('students.create') : null">
            <x-slot:actions>
                @if ($user->isAdmin())
                    <button type="button"
                            @click="importModalOpen = true"
                            class="edudash-btn-secondary gap-2 text-xs">
                        <x-heroicon-o-arrow-up-tray class="size-4 stroke-[1.5] text-muted" />
                        <span>Impor CSV</span>
                    </button>
                @endif
                <a href="{{ route('students.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="edudash-btn-secondary gap-2 text-xs">
                    <x-heroicon-o-arrow-down-tray class="size-4 stroke-[1.5] text-muted" />
                    <span>Ekspor CSV</span>
                </a>
                <a href="{{ route('students.index', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="edudash-btn-secondary gap-2 text-xs">
                    <x-heroicon-o-document-arrow-down class="size-4 stroke-[1.5] text-muted" />
                    <span>Ekspor PDF</span>
                </a>
                @if ($user->isAdmin())
                    <a href="{{ route('archive.index', ['tab' => 'students']) }}" wire:navigate
                       class="edudash-btn-secondary gap-2 text-xs">
                        <x-heroicon-o-archive-box class="size-4 stroke-[1.5] text-muted" />
                        <span>Arsip ({{ format_number($archivedCount) }})</span>
                    </a>
                @endif
            </x-slot:actions>
        </x-page-header>

        <x-card>
            <!-- Filter & Search Bar -->
            <form action="{{ route('students.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex flex-wrap items-center gap-2.5 flex-1">
                    <div class="relative flex-1 min-w-[220px] max-w-xs">
                        <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
                        <input type="search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Cari nama, kode, atau email"
                               aria-label="Cari data siswa"
                               class="w-full h-10 pl-9 pr-3.5 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                    </div>

                    <select name="class_id"
                            onchange="this.form.submit()"
                            aria-label="Filter berdasarkan kelas"
                            class="h-10 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                        <option value="">Semua kelas ({{ format_number(count($classes)) }})</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}" @selected((string) $classId === (string) $cls->id)>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status"
                            onchange="this.form.submit()"
                            aria-label="Filter berdasarkan status"
                            class="h-10 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                        <option value="">Semua status</option>
                        <option value="Aktif" @selected($status === 'Aktif')>Aktif</option>
                        <option value="Pindah" @selected($status === 'Pindah')>Pindah</option>
                        <option value="Menunggu" @selected($status === 'Menunggu')>Menunggu</option>
                    </select>

                    <select name="sort"
                            onchange="this.form.submit()"
                            aria-label="Urutkan data siswa"
                            class="h-10 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-normal text-secondary focus:border-primary focus:outline-none">
                        <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Nama A–Z</option>
                        <option value="code_asc" @selected($sort === 'code_asc')>Kode siswa</option>
                    </select>
                </div>

                <span class="font-mono text-xs text-muted tabular-nums">
                    {{ format_number($students->total()) }} siswa
                </span>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-normal text-muted">
                            <th class="py-3 pl-2 text-right w-10">No</th>
                            <th class="py-3 px-3">Kode</th>
                            <th class="py-3 px-3">Nama Siswa & Alamat</th>
                            <th class="py-3 px-3">Email Akun</th>
                            <th class="py-3 px-3">Kelas</th>
                            <th class="py-3 px-3">Terdaftar</th>
                            <th class="py-3 px-3">Status</th>
                            @if ($user->isAdmin())
                                <th class="py-3 pr-2 text-right">Tindakan</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($students as $idx => $student)
                            <tr class="group hover:bg-sidebar transition-colors duration-150">
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
                                @if ($user->isAdmin())
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
                                <td colspan="8">
                                    <x-empty-state
                                        title="Data siswa tidak ditemukan"
                                        description="Tidak ada siswa yang cocok dengan filter pencarian Anda"
                                        actionLabel="Reset filter"
                                        :actionUrl="route('students.index')" />
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
        </x-card>

        <!-- Modal Import CSV Siswa -->
        @if ($user->isAdmin())
            <div x-show="importModalOpen"
                 x-transition.opacity.duration.150ms
                 style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 p-4">
                <div @click.outside="importModalOpen = false"
                     class="bg-surface rounded-2xl p-6 max-w-md w-full shadow-xl border border-line">
                    <h3 class="text-base font-medium text-ink">Impor Data Siswa (CSV)</h3>
                    <p class="text-xs text-secondary mt-1 leading-relaxed">
                        Unggah berkas CSV dengan urutan kolom: <code class="edudash-keycap">student_code, name, status</code>
                    </p>
                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-xs text-secondary file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-line file:bg-sidebar file:text-ink file:font-medium" />
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="importModalOpen = false" class="edudash-btn-secondary h-9 px-4 text-xs">Batal</button>
                            <button type="submit" class="edudash-btn-primary h-9 px-4 text-xs">Impor CSV</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
