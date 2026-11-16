<x-layouts.app title="Data Guru" :breadcrumbs="[['label' => 'Data Guru', 'url' => route('teachers.index')], ['label' => 'Daftar Guru Pengajar']]">
    @php
        $user = auth()->user();
    @endphp

    <!-- Header Modul Data Guru (Sesuai Artboard 04 di FigCraft) -->
    <x-page-header
        title="Data Guru"
        subtitle="Kelola data guru pengajar, mata pelajaran (JOIN tbl_subjects), dan akun pengguna."
        :ctaLabel="$user->isAdmin() ? 'Tambah Guru' : null"
        :ctaUrl="$user->isAdmin() ? route('teachers.create') : null">
        <x-slot:actions>
            <a href="{{ route('teachers.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="edudash-btn-secondary inline-flex items-center gap-2 text-xs">
                <x-heroicon-o-arrow-down-tray class="size-4" />
                <span>Ekspor CSV</span>
            </a>
            @if ($user->isAdmin())
                <a href="{{ route('archive.index', ['tab' => 'teachers']) }}" wire:navigate
                   class="px-4 py-2.5 rounded-full bg-primary-soft text-primary font-semibold text-xs inline-flex items-center gap-1.5 hover:bg-primary hover:text-white transition">
                    <x-heroicon-o-archive-box class="size-4" />
                    <span>Archive ({{ format_number($stats['archived']) }})</span>
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <!-- KPI Strip Guru (4 Kolom) -->
    <x-card class="mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-4 lg:divide-x lg:divide-line">
            <x-stat-card label="Total Guru Pengajar" :value="$stats['total']" delta="+2 guru baru bulan ini" deltaType="success" />
            <x-stat-card label="Status Mengajar Aktif" :value="$stats['active']" delta="Aktif mengajar semester ini" deltaType="success" />
            <x-stat-card label="Mata Pelajaran Diampu" :value="$stats['subjects']" delta="Seluruh mapel terisi guru" deltaType="primary" />
            <x-stat-card label="Kelas Binaan" :value="$stats['classes']" delta="Kelas 10, 11, dan 12 IPA/IPS" deltaType="muted" />
        </div>
    </x-card>

    <!-- Kartu Tabel Data Guru -->
    <x-card class="mb-6">
        <form action="{{ route('teachers.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative flex-1 min-w-[240px] max-w-sm">
                    <x-heroicon-o-magnifying-glass class="size-4 text-muted absolute left-4 top-3 pointer-events-none" />
                    <input type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari nama guru, telepon, atau mapel…"
                           aria-label="Cari data guru"
                           class="w-full h-10 pl-10 pr-4 rounded-full bg-sidebar border border-primary/50 text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                </div>

                <select name="subject_id"
                        onchange="this.form.submit()"
                        aria-label="Filter mata pelajaran"
                        class="h-10 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-ink focus:border-primary focus:outline-none">
                    <option value="">Semua Mapel ({{ format_number(count($subjects)) }})</option>
                    @foreach ($subjects as $sub)
                        <option value="{{ $sub->id }}" @selected((string) $subjectId === (string) $sub->id)>
                            {{ $sub->code }} · {{ $sub->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status"
                        onchange="this.form.submit()"
                        aria-label="Filter status guru"
                        class="h-10 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-ink focus:border-primary focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected($status === 'Aktif')>Aktif</option>
                    <option value="Cuti" @selected($status === 'Cuti')>Cuti</option>
                    <option value="Pindah" @selected($status === 'Pindah')>Pindah</option>
                </select>

                <select name="sort"
                        onchange="this.form.submit()"
                        aria-label="Urutkan data guru"
                        class="h-10 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-muted focus:border-primary focus:outline-none">
                    <option value="name_asc" @selected($sort === 'name_asc')>Urutkan: Nama A–Z</option>
                    <option value="latest" @selected($sort === 'latest')>Urutkan: Terbaru</option>
                </select>
            </div>

            <span class="px-4 py-1.5 rounded-full bg-primary-soft text-primary text-xs font-semibold">
                Total: {{ format_number($teachers->total()) }} Guru
            </span>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-line text-xs font-medium text-muted">
                        <th class="py-3 pl-2 text-right w-12">No.</th>
                        <th class="py-3 px-3">ID Guru</th>
                        <th class="py-3 px-3">Nama Guru & Akun (JOIN tbl_users)</th>
                        <th class="py-3 px-3">Mata Pelajaran (JOIN tbl_subjects)</th>
                        <th class="py-3 px-3">No. Telepon</th>
                        <th class="py-3 px-3">Terdaftar</th>
                        <th class="py-3 px-3">Status</th>
                        @if ($user->isAdmin())
                            <th class="py-3 pr-2 text-right">Aksi CRUD</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-sm">
                    @forelse ($teachers as $idx => $teacher)
                        <tr class="group hover:bg-page/60 transition duration-150">
                            <td class="py-3.5 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                {{ format_number(($teachers->currentPage() - 1) * $teachers->perPage() + $idx + 1) }}
                            </td>
                            <td class="py-3.5 px-3 text-xs font-medium text-muted whitespace-nowrap">
                                {{ $teacher->teacher_code }}
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$teacher->name" :src="$teacher->img_url" />
                                    <div>
                                        <p class="text-[13px] font-semibold text-ink">{{ $teacher->name }}</p>
                                        <p class="text-[11px] text-muted truncate max-w-[260px]">
                                            {{ $teacher->user?->email ?? '-' }} {{ $teacher->notes ? '· ' . Str::limit($teacher->notes, 34) : '' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                @if ($teacher->subject)
                                    <span class="px-3 py-1 rounded-full bg-primary-soft text-primary text-[11px] font-semibold">
                                        {{ $teacher->subject->code }} · {{ $teacher->subject->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-muted">–</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-xs text-ink font-medium whitespace-nowrap">
                                {{ $teacher->phone ?? '–' }}
                            </td>
                            <td class="py-3.5 px-3 text-xs text-muted whitespace-nowrap">
                                {{ format_date($teacher->created_at) }}
                            </td>
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <x-status-badge :status="$teacher->status" />
                            </td>
                            @if ($user->isAdmin())
                                <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('teachers.edit', $teacher) }}" wire:navigate
                                           class="px-3 py-1 rounded-full bg-primary-soft text-primary text-[11px] font-semibold hover:bg-primary hover:text-white transition">
                                            Edit
                                        </a>
                                        <button type="button"
                                                @click="openArchiveModal('{{ route('teachers.archive', $teacher) }}', '{{ addslashes($teacher->name) }}', '{{ $teacher->teacher_code }}')"
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
                                    title="Data Guru Tidak Ditemukan"
                                    description="Tidak ada guru yang cocok dengan kata kunci atau filter mata pelajaran."
                                    actionLabel="Reset Filter"
                                    :actionUrl="route('teachers.index')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted">
            <span>
                Menampilkan {{ format_number($teachers->firstItem() ?? 0) }}–{{ format_number($teachers->lastItem() ?? 0) }}
                dari {{ format_number($teachers->total()) }} guru · Eager Loading <code class="text-ink">with(['user', 'subject'])</code>
            </span>
            <div>
                {{ $teachers->links() }}
            </div>
        </div>
    </x-card>
</x-layouts.app>
