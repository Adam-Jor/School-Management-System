<x-layouts.app title="Dashboard Admin" :breadcrumbs="[['label' => 'Dashboard Admin']]">
    <!-- Context & Period Headline -->
    <x-page-header
        title="Ringkasan Akademik · TA 2026/2027"
        :subtitle="'Data operasional siswa, guru, dan rombongan belajar per ' . format_date(now())"
        :isHero="true"
        ctaLabel="Tambah Siswa"
        :ctaUrl="route('students.create')"
    />

    <!-- Global Search Results Banner -->
    @if (!empty($globalQuery) && $globalResults)
        <x-card class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-medium text-ink">
                    Hasil pencarian untuk <span class="font-mono text-primary">"{{ $globalQuery }}"</span>
                </h2>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-xs font-medium text-muted hover:text-ink transition-colors duration-150">
                    Tutup hasil
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="bg-sidebar rounded-xl p-3.5 border border-line">
                    <p class="font-medium text-ink mb-2">Siswa ({{ count($globalResults['students']) }})</p>
                    @forelse ($globalResults['students'] as $st)
                        <div class="py-1.5 flex items-center justify-between border-b border-line last:border-none">
                            <span class="text-ink">{{ $st->name }}</span>
                            <span class="edudash-keycap">{{ $st->student_code }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada siswa cocok</p>
                    @endforelse
                </div>
                <div class="bg-sidebar rounded-xl p-3.5 border border-line">
                    <p class="font-medium text-ink mb-2">Guru ({{ count($globalResults['teachers']) }})</p>
                    @forelse ($globalResults['teachers'] as $tc)
                        <div class="py-1.5 flex items-center justify-between border-b border-line last:border-none">
                            <span class="text-ink">{{ $tc->name }}</span>
                            <span class="text-muted">{{ $tc->subject?->name }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada guru cocok</p>
                    @endforelse
                </div>
                <div class="bg-sidebar rounded-xl p-3.5 border border-line">
                    <p class="font-medium text-ink mb-2">Kelas ({{ count($globalResults['classes']) }})</p>
                    @forelse ($globalResults['classes'] as $cl)
                        <div class="py-1.5 flex items-center justify-between border-b border-line last:border-none">
                            <span class="text-ink">{{ $cl->name }}</span>
                            <span class="edudash-keycap">{{ $cl->code }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada kelas cocok</p>
                    @endforelse
                </div>
            </div>
        </x-card>
    @endif

    <!-- KPI Strip (1 card, 5 columns, tabular figures, real DB counts & deltas) -->
    <x-card class="mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-y-4 lg:divide-x lg:divide-line">
            <x-stat-card
                label="Pengguna terdaftar"
                :value="$stats['users']"
                :delta="'+' . format_number($stats['users_this_month'] ?? 0) . ' bln ini (vs ' . format_number($stats['users_last_month'] ?? 0) . ' bln lalu)'"
                deltaType="success" />
            <x-stat-card
                label="Total siswa"
                :value="$stats['students']"
                :delta="format_number($stats['students_active'] ?? 0) . ' aktif · +' . format_number($stats['students_this_month'] ?? 0) . ' bln ini'"
                deltaType="success" />
            <x-stat-card
                label="Guru pengajar"
                :value="$stats['teachers']"
                :delta="format_number($stats['teachers_active'] ?? 0) . ' aktif · +' . format_number($stats['teachers_this_month'] ?? 0) . ' bln ini'"
                deltaType="success" />
            <x-stat-card
                label="Rombongan belajar"
                :value="$stats['classes']"
                :delta="format_number($stats['classes_filled'] ?? 0) . ' dari ' . format_number($stats['classes']) . ' kelas terisi'"
                deltaType="primary" />
            <x-stat-card
                label="Mata pelajaran"
                :value="$stats['subjects']"
                :delta="format_number($stats['subjects_with_teachers'] ?? 0) . ' mapel ada pengajar'"
                deltaType="muted" />
        </div>
    </x-card>

    <!-- Middle Row: Main Chart (col-span-8) & Academic Registry Card (col-span-4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <!-- Main Chart -->
        <x-card class="lg:col-span-8 flex flex-col justify-between" x-data="{ openTable: false, chartMode: 'growth', payload: @js($chartData) }">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base font-medium text-ink">Tren & Distribusi Siswa</h2>
                        <p class="text-xs text-muted mt-0.5">Rekapitulasi data riil siswa per periode (kumulatif), kelas, dan status</p>
                    </div>
                    <select id="chartModeSelector"
                            x-model="chartMode"
                            @change="window.switchEdudashChartMode(chartMode)"
                            aria-label="Pilih mode grafik siswa"
                            class="h-9 px-3 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                        <option value="growth">Tren bulanan (12 bulan)</option>
                        <option value="class">Distribusi siswa per kelas</option>
                        <option value="status">Distribusi siswa per status</option>
                    </select>
                </div>

                <div class="h-[256px] w-full relative">
                    <canvas id="studentGrowthChart" aria-label="Grafik statistik siswa" role="img"></canvas>
                </div>
            </div>

            <!-- Accessible Data Table Collapse -->
            <div class="mt-4 pt-3 border-t border-line">
                <button type="button"
                        @click="openTable = !openTable"
                        class="text-xs font-normal text-muted hover:text-ink flex items-center gap-1.5 transition-colors duration-150">
                    <x-heroicon-o-table-cells class="size-4 stroke-[1.5]" />
                    <span x-text="openTable ? 'Sembunyikan tabel angka' : 'Tampilkan tabel angka (aksesibilitas)'">Tampilkan tabel angka (aksesibilitas)</span>
                </button>
                <div x-show="openTable" x-transition.opacity.duration.150ms style="display: none;" class="mt-3 overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="text-muted border-b border-line">
                                <template x-for="(lbl, idx) in (chartMode === 'class' ? payload.class_labels : (chartMode === 'status' ? payload.status_labels : payload.labels))" :key="'hdr-' + idx">
                                    <th class="py-1.5 px-2 text-right font-normal whitespace-nowrap" x-text="lbl"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <template x-for="(val, idx) in (chartMode === 'class' ? payload.class_values : (chartMode === 'status' ? payload.status_values : payload.values))" :key="'val-' + idx">
                                    <td class="py-1.5 px-2 text-right font-mono font-medium text-ink tabular-nums" x-text="Number(val).toLocaleString('id-ID')"></td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>

        <!-- Academic Registry Card -->
        <x-card class="lg:col-span-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-mono text-muted">REGISTRI TAHUN AJARAN</span>
                    <span class="edudash-keycap">GELOMBANG 1</span>
                </div>

                <div class="rounded-xl overflow-hidden border border-line">
                    <img src="{{ asset('img/welcome-illustration.svg') }}"
                         alt="Kartu Registrasi Akademik Siswa Baru"
                         class="w-full h-36 object-cover" />
                </div>

                <h2 class="text-base font-medium text-ink mt-4">Penerimaan & Penempatan Kelas</h2>
                <p class="text-xs text-secondary mt-1.5 leading-relaxed">
                    Verifikasi berkas siswa baru, pembagian rombongan belajar, dan sinkronisasi akun pengguna dilakukan terpusat melalui modul Data Siswa.
                </p>
            </div>

            <div class="mt-5 pt-3 border-t border-line flex items-center justify-between">
                <span class="font-mono text-xs text-muted tabular-nums">{{ format_number($stats['students_active'] ?? $stats['students'] ?? 0) }} siswa aktif</span>
                <a href="{{ route('students.index') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-ink hover:text-primary transition-colors duration-150">
                    <span>Buka direktori siswa</span>
                    <x-heroicon-o-arrow-right class="size-3.5 stroke-[1.5]" />
                </a>
            </div>
        </x-card>
    </div>

    <!-- Bottom Row: Latest Students Table (col-span-8) & Activity Log (col-span-4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Latest Students Table -->
        <x-card class="lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-base font-medium text-ink">Data Siswa Terbaru</h2>
                    <span class="font-mono text-xs text-muted tabular-nums">
                        {{ format_number($latestStudents->total()) }} terdaftar
                    </span>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <a href="{{ route('students.index', ['export' => 'csv']) }}"
                       class="inline-flex items-center gap-1.5 text-secondary hover:text-ink transition-colors duration-150">
                        <x-heroicon-o-arrow-down-tray class="size-4 stroke-[1.5]" />
                        <span>Unduh CSV</span>
                    </a>
                    <a href="{{ route('students.index') }}" wire:navigate
                       class="inline-flex items-center gap-1 text-primary font-medium hover:underline">
                        <span>Semua siswa</span>
                        <x-heroicon-o-arrow-right class="size-3.5 stroke-[1.5]" />
                    </a>
                </div>
            </div>

            <!-- Toolbar Search & Status Filter -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-4">
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-2.5 pointer-events-none" />
                    <input type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari nama atau kode"
                           aria-label="Cari siswa terbaru"
                           class="w-full h-9 pl-9 pr-3 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                </div>

                <select name="status"
                        onchange="this.form.requestSubmit()"
                        aria-label="Filter status siswa"
                        class="h-9 px-3 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                    <option value="">Semua status</option>
                    <option value="Aktif" @selected($statusFilter === 'Aktif')>Aktif</option>
                    <option value="Pindah" @selected($statusFilter === 'Pindah')>Pindah</option>
                    <option value="Menunggu" @selected($statusFilter === 'Menunggu')>Menunggu</option>
                </select>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-normal text-muted">
                            <th class="py-3 pl-2 text-right w-10">No</th>
                            <th class="py-3 px-3">Kode</th>
                            <th class="py-3 px-3">Nama Siswa</th>
                            <th class="py-3 px-3">Kelas</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 pr-2 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($latestStudents as $index => $student)
                            <tr class="group hover:bg-sidebar transition-colors duration-150">
                                <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                                    {{ format_number(($latestStudents->currentPage() - 1) * $latestStudents->perPage() + $index + 1) }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="edudash-keycap">{{ $student->student_code }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <x-avatar :name="$student->name" :src="$student->img_url" />
                                        <div>
                                            <p class="text-[13px] font-medium text-ink leading-tight">{{ $student->name }}</p>
                                            <p class="text-xs text-muted mt-0.5">
                                                {{ $student->user?->email ?? '–' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-[13px] text-secondary whitespace-nowrap">
                                    {{ $student->schoolClass?->name ?? '–' }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <x-status-badge :status="$student->status" />
                                </td>
                                <td class="py-3 pr-2 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <a href="{{ route('students.edit', $student) }}" wire:navigate
                                           aria-label="Edit {{ $student->name }}"
                                           class="size-8 rounded-lg text-muted hover:bg-raised hover:text-ink flex items-center justify-center transition-colors duration-150">
                                            <x-heroicon-o-pencil-square class="size-4 stroke-[1.5]" />
                                        </a>
                                        <button type="button"
                                                @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                                                aria-label="Arsipkan {{ $student->name }}"
                                                class="size-8 rounded-lg text-muted hover:bg-error-soft hover:text-error flex items-center justify-center transition-colors duration-150">
                                            <x-heroicon-o-archive-box class="size-4 stroke-[1.5]" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Data siswa belum tersedia" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="mt-4 pt-3 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
                <span>
                    Menampilkan {{ format_number($latestStudents->firstItem() ?? 0) }}–{{ format_number($latestStudents->lastItem() ?? 0) }}
                    dari {{ format_number($latestStudents->total()) }} siswa
                </span>
                <div>
                    {{ $latestStudents->links() }}
                </div>
            </div>
        </x-card>

        <!-- Activity Log Card -->
        <x-card id="aktivitas" class="lg:col-span-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-medium text-ink">Log Aktivitas</h2>
                    <span class="font-mono text-xs text-muted tabular-nums">{{ format_date(now()) }}</span>
                </div>

                <div class="divide-y divide-line">
                    @forelse ($activities as $act)
                        @php
                            $badgeColor = $act->getExtraProperty('badge_color')
                                ?? match ($act->event) {
                                    'created' => 'success',
                                    'deleted' => 'error',
                                    default => 'primary',
                                };
                            $dotColor = match ($badgeColor) {
                                'success' => 'bg-success',
                                'error' => 'bg-error',
                                'muted' => 'bg-muted',
                                default => 'bg-primary',
                            };
                            $targetLabel = $act->getExtraProperty('target_label')
                                ?? trim(($act->causer?->name ?? 'Sistem') . ' · ' . ($act->log_name ?? 'activity'));
                        @endphp
                        <div class="py-3 first:pt-1 last:pb-1 flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <span class="size-2 rounded-full {{ $dotColor }} mt-1.5 shrink-0"></span>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-medium text-ink truncate">{{ $act->description }}</p>
                                    <p class="text-xs text-muted truncate mt-0.5">{{ $targetLabel }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono text-muted whitespace-nowrap shrink-0 tabular-nums">
                                {{ $act->created_at ? format_date($act->created_at) : 'Baru saja' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-muted py-6 text-center">Belum ada aktivitas tercatat</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-line flex items-center justify-between text-xs text-muted tabular-nums">
                <span>{{ format_number(count($activities)) }} catatan terakhir</span>
                <span class="font-mono text-[11px]">Riwayat Audit Sistem</span>
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script>
            window.edudashChartPayload = @json($chartData);

            window.switchEdudashChartMode = function (mode) {
                const canvas = document.getElementById('studentGrowthChart');
                if (!canvas) return;
                if (typeof window.Chart === 'undefined') {
                    window.addEventListener('load', () => window.switchEdudashChartMode(mode), { once: true });
                    return;
                }

                if (canvas._chartInstance) {
                    canvas._chartInstance.destroy();
                }

                const ctx = canvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 240);
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.14)');
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

                let type = 'line';
                let labels = window.edudashChartPayload.labels;
                let data = window.edudashChartPayload.values;
                let bg = gradient;
                let border = '#111827';

                if (mode === 'class') {
                    type = 'bar';
                    labels = window.edudashChartPayload.class_labels;
                    data = window.edudashChartPayload.class_values;
                    bg = data.map((_, i) => i === 0 ? '#4F46E5' : '#CBD5E1');
                    border = 'transparent';
                } else if (mode === 'status') {
                    type = 'bar';
                    labels = window.edudashChartPayload.status_labels;
                    data = window.edudashChartPayload.status_values;
                    bg = ['#16A34A', '#94A3B8', '#CBD5E1'];
                    border = 'transparent';
                }

                canvas._chartInstance = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Siswa',
                            data: data,
                            borderColor: border,
                            borderWidth: type === 'line' ? 2 : 0,
                            borderRadius: type === 'bar' ? 6 : 0,
                            backgroundColor: bg,
                            fill: type === 'line',
                            tension: 0.35,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#4F46E5',
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
                                backgroundColor: '#111827',
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: false,
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6B7487', font: { family: 'monospace', size: 11 } } },
                            y: { border: { display: false, dash: [4, 4] }, grid: { color: '#E2E8F0' }, ticks: { color: '#6B7487', font: { family: 'monospace', size: 11 } } }
                        }
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', () => window.switchEdudashChartMode('growth'));
            document.addEventListener('livewire:navigated', () => window.switchEdudashChartMode('growth'));
        </script>
    @endpush
</x-layouts.app>
