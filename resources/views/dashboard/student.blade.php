<x-layouts.app title="Dashboard Siswa" :breadcrumbs="[['label' => 'Dashboard Siswa']]">
    @php
        $user = auth()->user();
        $totalAbsence = ($attendanceSummary['Izin'] ?? 0) + ($attendanceSummary['Sakit'] ?? 0) + ($attendanceSummary['Alpa'] ?? 0);
    @endphp

    <x-page-header
        title="Portal Akademik Siswa · TA 2026/2027"
        :subtitle="($student?->name ?? $user->name) . ($student?->schoolClass ? ' · Kelas ' . $student->schoolClass->name : '') . ' · ' . format_date(now())"
        :isHero="true"
        :ctaLabel="$student ? 'Unduh Rapor PDF' : null"
        :ctaUrl="$student ? route('grades.report.pdf', $student) : null"
    />

    <!-- Global Search Results Banner (Hanya Kelas & Data Sendiri sesuai Hak Akses Student) -->
    @if (!empty($globalQuery) && $globalResults)
        <x-card class="mb-4 sm:mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-medium text-ink">
                    Hasil pencarian untuk <span class="font-mono text-primary">"{{ $globalQuery }}"</span>
                </h2>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-xs font-medium text-muted hover:text-ink transition-colors duration-150">
                    Tutup hasil
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs">
                <div class="bg-sidebar rounded-xl p-3.5 border border-line">
                    <p class="font-medium text-ink mb-2">Data Pribadi Saya ({{ count($globalResults['students']) }})</p>
                    @forelse ($globalResults['students'] as $st)
                        <div class="py-1.5 flex items-center justify-between border-b border-line last:border-none">
                            <span class="text-ink">{{ $st->name }}</span>
                            <span class="edudash-keycap">{{ $st->student_code }}</span>
                        </div>
                    @empty
                        <p class="text-muted">Tidak ada kecocokan pada data pribadi Anda</p>
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

    <!-- Role Indicator Banner -->
    <div class="mb-4 sm:mb-6 px-3.5 sm:px-4 py-3 rounded-xl bg-surface border border-line flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
        <div class="flex items-start sm:items-center gap-2.5">
            <span class="size-2 rounded-full bg-success mt-1 sm:mt-0 shrink-0"></span>
            <div>
                <span class="font-medium text-ink">Portal Siswa (Student)</span>
                <span class="text-secondary block sm:inline sm:ml-1">Anda dapat melihat data pribadi, jadwal kelas, rekap kehadiran, rapor nilai, serta memperbarui profil pribadi</span>
            </div>
        </div>
        <a href="{{ route('profile.show') }}" wire:navigate class="font-medium text-primary hover:underline shrink-0">
            Edit profil saya →
        </a>
    </div>

    <!-- Personal Student KPI Strip (Mobile-First 2x2 Grid) -->
    <x-card class="mb-4 sm:mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-4 divide-x divide-line">
            <x-stat-card
                label="Total hadir"
                :value="$attendanceSummary['Hadir'] ?? 0"
                delta="Kehadiran tercatat"
                deltaType="success" />
            <x-stat-card
                label="Izin / Sakit / Alpa"
                :value="$totalAbsence"
                :delta="'I: ' . ($attendanceSummary['Izin'] ?? 0) . ' · S: ' . ($attendanceSummary['Sakit'] ?? 0) . ' · A: ' . ($attendanceSummary['Alpa'] ?? 0)"
                deltaType="muted" />
            <x-stat-card
                label="Rata-rata nilai"
                :value="(int) round($averageScore)"
                :delta="format_number(count($grades)) . ' mapel dinilai'"
                deltaType="primary" />
            <x-stat-card
                label="Rombongan belajar"
                :value="$totalClasses"
                :delta="'Kelas: ' . ($student?->schoolClass?->name ?? 'Belum diatur')"
                deltaType="primary" />
        </div>
    </x-card>

    <!-- Main Content: Kartu Pelajar Pribadi & Nilai Pribadi -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 mb-4 sm:mb-6 items-start">
        <x-card class="lg:col-span-4 space-y-4">
            <div class="flex items-center gap-3">
                <x-avatar :name="$student?->name ?? $user->name" :src="$student?->img_url ?? $user->img_url" size="lg" />
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-ink truncate">{{ $student?->name ?? $user->name }}</h2>
                    <p class="text-xs text-muted truncate">{{ $user->email }}</p>
                    @if ($student)
                        <span class="edudash-keycap mt-1 inline-block">{{ $student->student_code }}</span>
                    @endif
                </div>
            </div>

            <div class="pt-3 border-t border-line space-y-2.5 text-xs">
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Kelas Aktif</span>
                    <span class="font-medium text-ink">{{ $student?->schoolClass?->name ?? '–' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Status Siswa</span>
                    @if ($student)
                        <x-status-badge :status="$student->status" />
                    @else
                        <span class="text-muted">–</span>
                    @endif
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Telepon</span>
                    <span class="font-mono text-ink tabular-nums">{{ $student?->phone ?? $user->phone ?? '–' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Alamat</span>
                    <span class="text-secondary text-right max-w-[200px]">{{ $student?->address ?: '–' }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-line grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                <a href="{{ route('students.index') }}" wire:navigate class="edudash-btn-secondary w-full justify-between text-xs h-10 sm:h-9 px-3">
                    <span>Lihat Data Pribadi Saya</span>
                    <x-heroicon-o-arrow-right class="size-3.5" />
                </a>
                <a href="{{ route('classes.index') }}" wire:navigate class="edudash-btn-secondary w-full justify-between text-xs h-10 sm:h-9 px-3">
                    <span>Lihat Direktori Kelas</span>
                    <x-heroicon-o-arrow-right class="size-3.5" />
                </a>
            </div>
        </x-card>

        <x-card class="lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-medium text-ink">Nilai Rapor Akademik Pribadi</h2>
                    <p class="text-xs text-muted mt-0.5">Capaian hasil belajar per mata pelajaran untuk akun Anda</p>
                </div>
                <a href="{{ route('grades.index') }}" wire:navigate class="text-xs font-medium text-primary hover:underline">
                    Semua nilai →
                </a>
            </div>

            <!-- Mobile-First Grade Cards (< md) -->
            <div class="md:hidden space-y-2.5">
                @forelse ($grades as $grade)
                    <div class="rounded-xl border border-line bg-sidebar p-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink truncate">{{ $grade->subject?->name ?? '–' }}</p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <span class="edudash-keycap">{{ $grade->subject?->code ?? '–' }}</span>
                                <span class="text-xs text-muted">{{ $grade->semester }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0">
                            <span class="text-lg font-mono font-bold text-ink tabular-nums">{{ format_number($grade->score) }}</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-success-soft text-[#15803D] text-xs font-semibold">
                                {{ $grade->predicate }}
                            </span>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Belum ada nilai rapor yang diterbitkan" />
                @endforelse
            </div>

            <!-- Desktop Table (md+) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-normal text-muted">
                            <th class="py-2.5 pl-2">Mata Pelajaran</th>
                            <th class="py-2.5 px-3">Semester</th>
                            <th class="py-2.5 px-3 text-right">Nilai</th>
                            <th class="py-2.5 pr-2 text-center">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($grades as $grade)
                            <tr class="hover:bg-sidebar transition-colors duration-150">
                                <td class="py-3 pl-2">
                                    <p class="text-[13px] font-medium text-ink">{{ $grade->subject?->name ?? '–' }}</p>
                                    <p class="text-xs font-mono text-muted">{{ $grade->subject?->code ?? '–' }}</p>
                                </td>
                                <td class="py-3 px-3 text-xs text-secondary">{{ $grade->semester }}</td>
                                <td class="py-3 px-3 text-right font-mono font-semibold text-ink tabular-nums">
                                    {{ format_number($grade->score) }}
                                </td>
                                <td class="py-3 pr-2 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full bg-success-soft text-[#15803D] text-xs font-semibold">
                                        {{ $grade->predicate }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Belum ada nilai rapor yang diterbitkan" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Riwayat Absensi Pribadi -->
    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-medium text-ink">Riwayat Absensi Pribadi Terbaru</h2>
                <p class="text-xs text-muted mt-0.5">Rekap kehadiran harian Anda pada kelas aktif</p>
            </div>
            <a href="{{ route('attendances.index') }}" wire:navigate class="text-xs font-medium text-primary hover:underline">
                Lihat rekap bulanan →
            </a>
        </div>

        <!-- Mobile-First Attendance Cards (< md) -->
        <div class="md:hidden space-y-2.5">
            @forelse ($recentAttendances as $att)
                <div class="rounded-xl border border-line bg-sidebar p-3.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-ink tabular-nums">{{ format_date($att->date) }}</span>
                            <span class="edudash-keycap">{{ $att->schoolClass?->name ?? '–' }}</span>
                        </div>
                        <p class="text-xs text-muted mt-1 truncate">{{ $att->notes ?: 'Hadir sesuai jadwal pembelajaran' }}</p>
                    </div>
                    <div class="shrink-0">
                        <x-status-badge :status="$att->status" />
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada catatan absensi pribadi" />
            @endforelse
        </div>

        <!-- Desktop Table (md+) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-line text-xs font-normal text-muted">
                        <th class="py-2.5 pl-2">Tanggal</th>
                        <th class="py-2.5 px-3">Kelas</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 pr-2">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-sm">
                    @forelse ($recentAttendances as $att)
                        <tr class="hover:bg-sidebar transition-colors duration-150">
                            <td class="py-3 pl-2 font-mono text-xs text-ink tabular-nums">{{ format_date($att->date) }}</td>
                            <td class="py-3 px-3 text-xs text-secondary">{{ $att->schoolClass?->name ?? '–' }}</td>
                            <td class="py-3 px-3"><x-status-badge :status="$att->status" /></td>
                            <td class="py-3 pr-2 text-xs text-muted">{{ $att->notes ?: '–' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state title="Belum ada catatan absensi pribadi" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
