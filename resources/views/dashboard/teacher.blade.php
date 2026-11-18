<x-layouts.app title="Dashboard Pengajar" :breadcrumbs="[['label' => 'Dashboard Pengajar']]">
    @php
        $user = auth()->user();
    @endphp

    <x-page-header
        title="Ruang Mengajar · TA 2026/2027"
        :subtitle="($teacher?->name ?? $user->name) . ($teacher?->subject ? ' · Pengampu ' . $teacher->subject->name : '') . ' · ' . format_date(now())"
        :isHero="true"
        :ctaLabel="$taughtClasses->isNotEmpty() ? 'Input Absensi Kelas' : null"
        :ctaUrl="$taughtClasses->isNotEmpty() ? route('attendances.index', ['class_id' => $taughtClasses->first()->id]) : null"
    />

    <!-- Global Search Results Banner (Hanya Siswa & Kelas sesuai Hak Akses Teacher) -->
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
            <span class="size-2 rounded-full bg-primary mt-1 sm:mt-0 shrink-0"></span>
            <div>
                <span class="font-medium text-ink">Peran Pengajar (Teacher)</span>
                <span class="text-secondary block sm:inline sm:ml-1">Akses lihat data siswa, kelas, mata pelajaran, serta input absensi untuk kelas yang Anda ajar</span>
            </div>
        </div>
        <a href="{{ route('profile.show') }}" wire:navigate class="font-medium text-primary hover:underline shrink-0">
            Edit profil saya →
        </a>
    </div>

    <!-- Teacher KPI Strip (Mobile-First 2x2 Grid) -->
    <x-card class="mb-4 sm:mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-4 divide-x divide-line">
            <x-stat-card
                label="Kelas yang diajar"
                :value="$teacherStats['classes']"
                delta="Penugasan aktif"
                deltaType="primary" />
            <x-stat-card
                label="Siswa di kelas ampuan"
                :value="$teacherStats['students']"
                delta="Total peserta didik"
                deltaType="success" />
            <x-stat-card
                label="Absensi hari ini"
                :value="$teacherStats['today_recorded']"
                :delta="format_number($teacherStats['today_present']) . ' siswa Hadir'"
                deltaType="success" />
            <x-stat-card
                label="Kurikulum mapel"
                :value="$teacherStats['subjects']"
                :delta="$teacher?->subject ? $teacher->subject->name : 'Pengajar aktif'"
                deltaType="muted" />
        </div>
    </x-card>

    <!-- Jadwal Mengajar Hari Ini -->
    <x-card class="mb-4 sm:mb-6">
        <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-primary"></span>
                <h2 class="text-sm font-semibold text-ink">Jadwal Mengajar Hari Ini ({{ $todayName }})</h2>
            </div>
            <a href="{{ route('schedules.index') }}" wire:navigate class="text-xs font-medium text-primary hover:underline">
                Lihat Jadwal Lengkap →
            </a>
        </div>

        @if ($todaySchedules->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($todaySchedules as $sch)
                    @php $isOngoing = $sch->isOngoingNow(); @endphp
                    <div class="p-3.5 rounded-xl border {{ $isOngoing ? 'border-primary ring-2 ring-primary/10 bg-primary-soft/10' : 'border-line bg-sidebar' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="edudash-keycap">{{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}</span>
                                @if ($isOngoing)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 animate-pulse">Sedang Berlangsung</span>
                                @else
                                    <span class="text-xs font-semibold text-primary">{{ $sch->schoolClass?->name }}</span>
                                @endif
                            </div>
                            <p class="text-xs font-medium text-ink">{{ $sch->subject?->name }}</p>
                            @if ($sch->room)
                                <p class="text-[11px] text-muted mt-1">Ruang: {{ $sch->room }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-muted py-2">Tidak ada jadwal mengajar pada hari {{ $todayName }}. Nikmati hari persiapan Anda!</p>
        @endif
    </x-card>

    <!-- Middle Row: Kelas yang Diajar (Prioritas Utama di Mobile) + Kartu Profil Pengajar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 mb-4 sm:mb-6 items-start">
        <x-card class="lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-medium text-ink">Kelas yang Sedang Anda Ajar</h2>
                    <p class="text-xs text-muted mt-0.5">Ketuk tombol Absen Kelas untuk mencatat kehadiran siswa</p>
                </div>
                <span class="edudash-keycap">{{ format_date(now()) }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                @forelse ($taughtClasses as $cls)
                    @php
                        $todayStat = $todayAttendanceMap[$cls->id] ?? ['recorded' => 0, 'present' => 0];
                        $progressPct = $cls->students_count > 0
                            ? min(100, (int) round(($todayStat['recorded'] / $cls->students_count) * 100))
                            : 0;
                    @endphp
                    <div class="rounded-xl border border-line bg-sidebar p-4 flex flex-col justify-between gap-3.5">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="edudash-keycap">{{ $cls->code }}</span>
                                <span class="text-xs font-mono text-muted tabular-nums">
                                    {{ format_number($cls->students_count) }}/{{ format_number($cls->capacity) }} siswa
                                </span>
                            </div>
                            <h3 class="text-base font-semibold text-ink mt-2">{{ $cls->name }}</h3>
                            <p class="text-xs text-secondary mt-1 line-clamp-2">
                                {{ $cls->description ?: 'Rombongan belajar aktif tahun ajaran 2026/2027' }}
                            </p>

                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] text-muted mb-1 tabular-nums">
                                    <span>Progress absensi hari ini</span>
                                    <span>{{ $progressPct }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-line overflow-hidden">
                                    <div class="h-full rounded-full bg-primary transition-all duration-300" style="width: {{ $progressPct }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-line flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <span class="text-xs text-muted tabular-nums">
                                @if ($todayStat['recorded'] > 0)
                                    <span class="text-[#15803D] font-medium">{{ format_number($todayStat['present']) }} hadir</span> · {{ format_number($todayStat['recorded']) }} tercatat
                                @else
                                    Belum diabsen hari ini
                                @endif
                            </span>
                            <a href="{{ route('attendances.index', ['class_id' => $cls->id]) }}" wire:navigate
                               class="h-10 sm:h-8 px-3.5 rounded-lg bg-primary text-white text-xs font-medium inline-flex items-center justify-center gap-1.5 hover:opacity-90 active:scale-[0.98] transition w-full sm:w-auto">
                                <x-heroicon-o-calendar-days class="size-4 sm:size-3.5 stroke-[1.75]" />
                                <span>Absen Kelas</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2">
                        <x-empty-state
                            title="Belum ada kelas yang ditugaskan"
                            description="Hubungi administrator sekolah untuk mengatur kelas yang Anda ajar" />
                    </div>
                @endforelse
            </div>
        </x-card>

        <x-card class="lg:col-span-4 space-y-4">
            <div class="flex items-center gap-3">
                <x-avatar :name="$teacher?->name ?? $user->name" :src="$teacher?->img_url ?? $user->img_url" size="lg" />
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-ink truncate">{{ $teacher?->name ?? $user->name }}</h2>
                    <p class="text-xs text-muted truncate">{{ $user->email }}</p>
                    @if ($teacher)
                        <span class="edudash-keycap mt-1 inline-block">{{ $teacher->teacher_code }}</span>
                    @endif
                </div>
            </div>

            <div class="pt-3 border-t border-line space-y-2.5 text-xs">
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Mata Pelajaran</span>
                    <span class="font-medium text-ink text-right">{{ $teacher?->subject?->name ?? '–' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Jumlah Kelas Ampuan</span>
                    <span class="font-mono font-medium text-primary tabular-nums">{{ format_number($teacherStats['classes']) }} kelas</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-muted">Catatan Mengajar</span>
                    <span class="text-secondary text-right">{{ $teacher?->notes ?: 'Guru Pengajar Aktif' }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-line grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                <a href="{{ route('subjects.index') }}" wire:navigate class="edudash-btn-secondary w-full justify-between text-xs h-10 sm:h-9 px-3">
                    <span>Lihat Mata Pelajaran</span>
                    <x-heroicon-o-arrow-right class="size-3.5" />
                </a>
                <a href="{{ route('classes.index') }}" wire:navigate class="edudash-btn-secondary w-full justify-between text-xs h-10 sm:h-9 px-3">
                    <span>Lihat Direktori Kelas</span>
                    <x-heroicon-o-arrow-right class="size-3.5" />
                </a>
            </div>
        </x-card>
    </div>

    <!-- Bottom Row: Daftar Siswa di Kelas Ampuan (Mobile Cards + Desktop Table) -->
    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-medium text-ink">Siswa di Kelas yang Anda Ajar</h2>
                <p class="text-xs text-muted mt-0.5">Daftar peserta didik pada kelas ampuan Anda (tampilan baca)</p>
            </div>
            <form action="{{ route('dashboard') }}" method="GET" class="relative w-full sm:w-64">
                <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
                <input type="search"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Cari siswa di kelas ampuan"
                       aria-label="Cari siswa di kelas yang diajar"
                       class="w-full h-10 pl-9 pr-3 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
            </form>
        </div>

        <!-- Mobile-First Card List (< md) -->
        <div class="md:hidden space-y-2.5">
            @forelse ($classStudents as $index => $student)
                <div class="rounded-xl border border-line bg-sidebar p-3.5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-avatar :name="$student->name" :src="$student->img_url" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink truncate">{{ $student->name }}</p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <span class="edudash-keycap">{{ $student->student_code }}</span>
                                <span class="text-xs text-secondary">{{ $student->schoolClass?->name ?? '–' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <x-status-badge :status="$student->status" />
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada data siswa di kelas ampuan" />
            @endforelse
        </div>

        <!-- Desktop Table (md+) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-line text-xs font-normal text-muted">
                        <th class="py-3 pl-2 text-right w-10">No</th>
                        <th class="py-3 px-3">Kode Siswa</th>
                        <th class="py-3 px-3">Nama Siswa</th>
                        <th class="py-3 px-3">Kelas</th>
                        <th class="py-3 pr-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-sm">
                    @forelse ($classStudents as $index => $student)
                        <tr class="hover:bg-sidebar transition-colors duration-150">
                            <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                                {{ format_number(($classStudents->currentPage() - 1) * $classStudents->perPage() + $index + 1) }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="edudash-keycap">{{ $student->student_code }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2.5">
                                    <x-avatar :name="$student->name" :src="$student->img_url" />
                                    <div>
                                        <p class="text-[13px] font-medium text-ink">{{ $student->name }}</p>
                                        <p class="text-xs text-muted">{{ $student->user?->email ?? '–' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-[13px] text-secondary whitespace-nowrap">
                                {{ $student->schoolClass?->name ?? '–' }}
                            </td>
                            <td class="py-3 pr-2 whitespace-nowrap">
                                <x-status-badge :status="$student->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Belum ada data siswa di kelas ampuan" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-3 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
            <span>
                Menampilkan {{ format_number($classStudents->firstItem() ?? 0) }}–{{ format_number($classStudents->lastItem() ?? 0) }}
                dari {{ format_number($classStudents->total()) }} siswa
            </span>
            <div>{{ $classStudents->links() }}</div>
        </div>
    </x-card>
</x-layouts.app>
