<x-layouts.app title="Dashboard" :breadcrumbs="[['label' => 'Dashboard']]">
    @php
        $user = auth()->user();
    @endphp

    <!-- Hero Header (design.md 4.3: hanya dipakai di Dashboard, satu tombol utama) -->
    <x-page-header
        :title="'Selamat datang, ' . Str::words($user->name, 2, '') . '.'"
        :subtitle="'Ringkasan data siswa, guru, dan kelas hari ini — ' . format_date(now()) . '.'"
        :isHero="true"
        :ctaLabel="$user->isAdmin() ? 'Tambah Siswa' : null"
        :ctaUrl="$user->isAdmin() ? route('students.create') : null"
    />

    <!-- Global Search Results Banner (if global_q is searched) -->
    @if (!empty($globalQuery) && $globalResults)
        <x-card class="mb-6 border border-primary/30">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-ink">
                    Hasil Pencarian Global: <span class="text-primary">"{{ $globalQuery }}"</span>
                </h2>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-xs font-semibold text-muted hover:text-ink">
                    Reset Pencarian ✕
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="bg-sidebar rounded-2xl p-3.5 border border-line">
                    <p class="font-semibold text-ink mb-2">Siswa ({{ count($globalResults['students']) }})</p>
                    @forelse ($globalResults['students'] as $st)
                        <div class="py-1 flex items-center justify-between border-b border-line/60 last:border-none">
                            <span class="font-medium text-ink">{{ $st->name }}</span>
                            <span class="text-muted font-mono">{{ $st->student_code }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada siswa cocok.</p>
                    @endforelse
                </div>
                <div class="bg-sidebar rounded-2xl p-3.5 border border-line">
                    <p class="font-semibold text-ink mb-2">Guru ({{ count($globalResults['teachers']) }})</p>
                    @forelse ($globalResults['teachers'] as $tc)
                        <div class="py-1 flex items-center justify-between border-b border-line/60 last:border-none">
                            <span class="font-medium text-ink">{{ $tc->name }}</span>
                            <span class="text-muted">{{ $tc->subject?->name }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada guru cocok.</p>
                    @endforelse
                </div>
                <div class="bg-sidebar rounded-2xl p-3.5 border border-line">
                    <p class="font-semibold text-ink mb-2">Kelas ({{ count($globalResults['classes']) }})</p>
                    @forelse ($globalResults['classes'] as $cl)
                        <div class="py-1 flex items-center justify-between border-b border-line/60 last:border-none">
                            <span class="font-medium text-ink">{{ $cl->name }}</span>
                            <span class="text-muted font-mono">{{ $cl->code }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada kelas cocok.</p>
                    @endforelse
                </div>
            </div>
        </x-card>
    @endif

    <!-- Role Indicator Banner for Teacher / Student (Stage 1 item 4) -->
    @if (! $user->isAdmin())
        <div class="mb-6 px-5 py-3.5 rounded-full bg-primary-soft border border-primary/20 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="size-2 rounded-full bg-primary"></span>
                <span class="font-semibold text-ink">Mode Akses {{ ucfirst($user->role) }} (Read-Only):</span>
                <span class="text-muted">Anda dapat melihat seluruh ringkasan data dan laporan, namun aksi tambah/ubah/hapus hanya tersedia bagi Administrator.</span>
            </div>
            <a href="{{ route('profile.show') }}" wire:navigate class="font-semibold text-primary hover:underline">
                Lihat Data Pribadi Saya →
            </a>
        </div>
    @endif

    <!-- KPI Strip (design.md 4.4: 1 kartu putih, 5 kolom dipisah garis vertikal tipis) -->
    <x-card class="mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-y-4 lg:divide-x lg:divide-line">
            <x-stat-card label="Pengguna" :value="$stats['users']" delta="+8 bulan ini" deltaType="success" />
            <x-stat-card label="Siswa" :value="$stats['students']" delta="+24 bulan ini" deltaType="success" />
            <x-stat-card label="Guru" :value="$stats['teachers']" delta="+2 bulan ini" deltaType="success" />
            <x-stat-card label="Kelas" :value="$stats['classes']" delta="4 kelas baru" deltaType="primary" />
            <x-stat-card label="Mata Pelajaran" :value="$stats['subjects']" delta="– Kurikulum aktif" deltaType="muted" />
        </div>
    </x-card>

    <!-- Row Tengah: Chart (col-span-8) & Kartu Promo (col-span-4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <!-- Kartu Chart (design.md 4.5) -->
        <x-card class="lg:col-span-8 flex flex-col justify-between">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-ink">Pertumbuhan Siswa</h2>
                        <p class="text-xs text-muted mt-0.5">Siswa aktif · Bulanan</p>
                    </div>
                    <span class="px-4 py-1.5 rounded-full bg-sidebar border border-line text-xs font-medium text-ink">
                        Satu tahun ajaran ▾
                    </span>
                </div>

                <div class="h-[240px] w-full relative">
                    <canvas id="studentGrowthChart" aria-label="Grafik pertumbuhan siswa bulanan" role="img"></canvas>
                </div>
            </div>

            <!-- Disclosure Aksesibilitas (design.md 4.5 & 7) -->
            <div class="mt-4 pt-3 border-t border-line" x-data="{ openTable: false }">
                <button type="button"
                        @click="openTable = !openTable"
                        class="text-xs font-medium text-muted hover:text-ink flex items-center gap-1.5">
                    <span x-text="openTable ? '▾' : '▸'">▸</span>
                    <span>Lihat data grafik (tabel angka aksesibilitas)</span>
                </button>
                <div x-show="openTable" x-transition style="display: none;" class="mt-3 overflow-x-auto">
                    <table class="table table-xs w-full text-xs">
                        <thead>
                            <tr class="text-muted border-b border-line">
                                @foreach ($chartData['labels'] as $lbl)
                                    <th class="text-right font-medium">{{ $lbl }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach ($chartData['values'] as $val)
                                    <td class="text-right font-semibold text-ink">{{ format_number($val) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>

        <!-- Kartu Promo / Pengumuman (design.md 4.6) -->
        <div class="lg:col-span-4 bg-promo rounded-[20px] p-6 flex flex-col justify-between">
            <div>
                <div class="bg-[#E1F2FE] rounded-2xl p-2 overflow-hidden">
                    <img src="{{ asset('img/welcome-illustration.svg') }}"
                         alt="Ilustrasi Penerimaan Siswa Baru"
                         class="w-full h-36 object-contain" />
                </div>

                <h2 class="text-lg font-bold text-ink mt-5">Penerimaan Siswa Baru</h2>
                <p class="text-[13px] text-ink/80 mt-2 leading-relaxed">
                    Pendaftaran tahun ajaran 2026/2027 telah dibuka. Pantau kuota kelas dan verifikasi berkas siswa baru secara terpusat.
                </p>
            </div>

            <div class="mt-5">
                <a href="{{ route('students.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 text-sm font-semibold text-ink hover:text-primary transition">
                    <span>Lihat data siswa baru</span>
                    <x-heroicon-o-arrow-right class="size-4" />
                </a>
            </div>
        </div>
    </div>

    <!-- Row Bawah: Tabel Data Siswa Terbaru (col-span-8) & Aktivitas Terbaru (col-span-4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kartu Tabel Data Siswa Terbaru (design.md 4.7) -->
        <x-card class="lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-base font-semibold text-ink">Data Siswa Terbaru</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-primary-soft text-primary text-xs font-semibold">
                        {{ format_number($latestStudents->total()) }}
                    </span>
                </div>

                <div class="flex items-center gap-4 text-[13px]">
                    <a href="{{ route('students.index', ['export' => 'csv']) }}"
                       class="inline-flex items-center gap-1.5 text-muted font-medium hover:text-ink transition">
                        <x-heroicon-o-arrow-down-tray class="size-4" />
                        <span>Ekspor</span>
                    </a>
                    <a href="{{ route('students.index') }}" wire:navigate
                       class="inline-flex items-center gap-1 text-primary font-semibold hover:underline">
                        <span>Lihat Semua</span>
                        <x-heroicon-o-arrow-right class="size-3.5" />
                    </a>
                </div>
            </div>

            <!-- Toolbar Search & Status Filter -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-4">
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <x-heroicon-o-magnifying-glass class="size-4 text-muted absolute left-3.5 top-2.5 pointer-events-none" />
                    <input type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari siswa…"
                           aria-label="Cari siswa terbaru"
                           class="w-full h-9 pl-9 pr-4 rounded-full bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                </div>

                <select name="status"
                        onchange="this.form.submit()"
                        aria-label="Filter status siswa"
                        class="h-9 px-4 rounded-full bg-sidebar border border-line text-xs font-medium text-ink focus:border-primary focus:outline-none">
                    <option value="">Semua status</option>
                    <option value="Aktif" @selected($statusFilter === 'Aktif')>Aktif</option>
                    <option value="Pindah" @selected($statusFilter === 'Pindah')>Pindah</option>
                    <option value="Menunggu" @selected($statusFilter === 'Menunggu')>Menunggu</option>
                </select>
            </form>

            <!-- Tabel Tanpa Garis Vertikal (design.md 4.7) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-medium text-muted">
                            <th class="py-3 pl-2 w-8">
                                <span class="size-3.5 rounded-full border border-muted/60 inline-block"></span>
                            </th>
                            <th class="py-3 px-2">ID</th>
                            <th class="py-3 px-2">Nama Siswa</th>
                            <th class="py-3 px-2">Kelas</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 pr-2 text-right">No. Urut</th>
                            @if ($user->isAdmin())
                                <th class="py-3 pr-2 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($latestStudents as $index => $student)
                            <tr class="group hover:bg-page/60 transition duration-150">
                                <td class="py-3.5 pl-2">
                                    <span class="size-3.5 rounded-full border border-line group-hover:border-primary inline-block"></span>
                                </td>
                                <td class="py-3.5 px-2 text-xs font-medium text-muted whitespace-nowrap">
                                    {{ $student->student_code }}
                                </td>
                                <td class="py-3.5 px-2">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$student->name" :src="$student->img_url" />
                                        <div>
                                            <p class="text-[13px] font-semibold text-ink leading-tight">{{ $student->name }}</p>
                                            <p class="text-[11px] text-muted mt-0.5">
                                                {{ $student->schoolClass?->name ?? 'Belum ada kelas' }} · {{ $student->user?->email ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-2 text-[13px] font-medium text-ink whitespace-nowrap">
                                    {{ $student->schoolClass?->name ?? '–' }}
                                </td>
                                <td class="py-3.5 px-2 whitespace-nowrap">
                                    <x-status-badge :status="$student->status" />
                                </td>
                                <td class="py-3.5 pr-2 text-right text-xs font-semibold text-muted tabular-nums">
                                    {{ format_number(($latestStudents->currentPage() - 1) * $latestStudents->perPage() + $index + 1) }}
                                </td>
                                @if ($user->isAdmin())
                                    <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-1.5 opacity-80 group-hover:opacity-100">
                                            <a href="{{ route('students.edit', $student) }}" wire:navigate
                                               aria-label="Edit {{ $student->name }}"
                                               class="size-8 rounded-full bg-primary-soft text-primary flex items-center justify-center hover:bg-primary hover:text-white transition">
                                                <x-heroicon-o-pencil-square class="size-4" />
                                            </a>
                                            <button type="button"
                                                    @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                                                    aria-label="Archive {{ $student->name }}"
                                                    class="size-8 rounded-full bg-error-soft text-error flex items-center justify-center hover:bg-error hover:text-white transition">
                                                <x-heroicon-o-archive-box class="size-4" />
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="Data siswa belum tersedia" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel & Pagination -->
            <div class="mt-4 pt-3 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted">
                <span>
                    Menampilkan {{ format_number($latestStudents->firstItem() ?? 0) }}–{{ format_number($latestStudents->lastItem() ?? 0) }}
                    dari {{ format_number($latestStudents->total()) }} siswa ({{ format_date(now()) }})
                </span>
                <div>
                    {{ $latestStudents->links() }}
                </div>
            </div>
        </x-card>

        <!-- Kartu Aktivitas Terbaru (design.md 4.8) -->
        <x-card id="aktivitas" class="lg:col-span-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-ink">Aktivitas Terbaru</h2>
                    <span class="size-7 rounded-full bg-primary-soft text-primary flex items-center justify-center">
                        <x-heroicon-o-bolt class="size-4" />
                    </span>
                </div>

                <div class="divide-y divide-line">
                    @forelse ($activities as $act)
                        @php
                            $dotColor = match ($act->badge_color) {
                                'success' => 'bg-success',
                                'error' => 'bg-error',
                                'muted' => 'bg-muted',
                                default => 'bg-primary',
                            };
                        @endphp
                        <div class="py-3.5 first:pt-1 last:pb-1 flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <span class="size-2 rounded-full {{ $dotColor }} mt-1.5 shrink-0"></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-ink truncate">{{ $act->action }}</p>
                                    <p class="text-xs text-muted truncate mt-0.5">{{ $act->target_label }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono text-muted whitespace-nowrap shrink-0">
                                {{ $act->created_at ? format_date($act->created_at) : 'Baru saja' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-muted py-6 text-center">Belum ada aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-line flex items-center justify-between text-xs text-muted">
                <span>● {{ format_number(count($activities)) }} aktivitas terbaru tercatat</span>
                <span>{{ format_date(now()) }}</span>
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', renderEdudashChart);
            document.addEventListener('livewire:navigated', renderEdudashChart);

            function renderEdudashChart() {
                const canvas = document.getElementById('studentGrowthChart');
                if (!canvas || typeof Chart === 'undefined') return;

                if (canvas._chartInstance) {
                    canvas._chartInstance.destroy();
                }

                const ctx = canvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                gradient.addColorStop(0, 'rgba(109, 91, 232, 0.22)');
                gradient.addColorStop(1, 'rgba(109, 91, 232, 0.01)');

                canvas._chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [{
                            label: 'Siswa Aktif',
                            data: @json($chartData['values']),
                            borderColor: '#161B33',
                            borderWidth: 2.5,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#6D5BE8',
                            pointHoverBorderColor: '#FFFFFF',
                            pointHoverBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#161B33',
                                titleFont: { family: 'Poppins', size: 11, weight: '600' },
                                bodyFont: { family: 'Poppins', size: 11 },
                                padding: 10,
                                cornerRadius: 999,
                                displayColors: false,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#6B7085', font: { family: 'Poppins', size: 11 } }
                            },
                            y: {
                                border: { display: false, dash: [4, 4] },
                                grid: { color: '#E3E6F6' },
                                ticks: { color: '#6B7085', font: { family: 'Poppins', size: 11 } }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</x-layouts.app>
