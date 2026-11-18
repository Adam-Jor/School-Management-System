<x-layouts.app :title="$title" :breadcrumbs="$breadcrumbs">
    @php
        $user = auth()->user();
        $waliKelas = $schoolClass->teachers->first();
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="edudash-keycap text-xs">{{ $schoolClass->code }}</span>
                <h1 class="text-xl sm:text-2xl font-bold text-ink">Rombongan Belajar {{ $schoolClass->name }}</h1>
            </div>
            <p class="text-xs text-muted mt-1">
                Wali Kelas: <span class="font-medium text-ink">{{ $waliKelas?->name ?? 'Belum ditentukan' }}</span>
                · Kapasitas: <span class="font-mono text-ink font-semibold">{{ $schoolClass->students->count() }}/{{ $schoolClass->capacity ?? 36 }}</span> Siswa
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if (!$user->isStudent())
                <a href="{{ route('attendances.index', ['class_id' => $schoolClass->id]) }}" wire:navigate
                   class="edudash-btn-primary text-xs h-9 px-3 gap-1.5">
                    <x-heroicon-o-calendar-days class="size-4" />
                    <span>Absen Kelas Ini</span>
                </a>
            @endif
            @if ($user->isAdmin())
                <a href="{{ route('classes.edit', $schoolClass) }}" wire:navigate
                   class="edudash-btn-secondary text-xs h-9 px-3">
                    Edit Kelas
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-surface border border-line rounded-xl p-3.5 sm:p-4">
            <span class="text-xs text-muted">Total Siswa Terdaftar</span>
            <p class="text-xl font-bold text-ink mt-1 font-mono tabular-nums">{{ format_number($schoolClass->students->count()) }}</p>
            <span class="text-[11px] text-emerald-600 font-medium">{{ $schoolClass->students->where('status', 'Aktif')->count() }} Aktif</span>
        </div>
        <div class="bg-surface border border-line rounded-xl p-3.5 sm:p-4">
            <span class="text-xs text-muted">Guru Pengampu Mapel</span>
            <p class="text-xl font-bold text-ink mt-1 font-mono tabular-nums">{{ format_number($schoolClass->teachers->count()) }}</p>
            <span class="text-[11px] text-primary font-medium">Tenaga pendidik</span>
        </div>
        <div class="bg-surface border border-line rounded-xl p-3.5 sm:p-4">
            <span class="text-xs text-muted">Kehadiran Hari Ini</span>
            <p class="text-xl font-bold text-ink mt-1 font-mono tabular-nums">{{ $attendanceSummary['Hadir'] }} Hadir</p>
            <span class="text-[11px] text-muted">{{ $attendanceSummary['Sakit'] + $attendanceSummary['Izin'] + $attendanceSummary['Alpa'] }} Tidak Hadir</span>
        </div>
        <div class="bg-surface border border-line rounded-xl p-3.5 sm:p-4">
            <span class="text-xs text-muted">Sesi Jadwal Pelajaran</span>
            <p class="text-xl font-bold text-ink mt-1 font-mono tabular-nums">{{ format_number($schoolClass->schedules->count()) }}</p>
            <span class="text-[11px] text-secondary font-medium">Minggu aktif</span>
        </div>
    </div>

    <!-- Tabs Container -->
    <div x-data="{ tab: 'students' }" class="space-y-4">
        <!-- Tab Headers -->
        <div class="border-b border-line flex items-center gap-2 overflow-x-auto">
            <button type="button"
                    @click="tab = 'students'"
                    :class="tab === 'students' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary hover:text-ink'"
                    class="px-4 py-2.5 border-b-2 text-xs transition-colors whitespace-nowrap">
                Daftar Siswa ({{ $schoolClass->students->count() }})
            </button>
            <button type="button"
                    @click="tab = 'teachers'"
                    :class="tab === 'teachers' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary hover:text-ink'"
                    class="px-4 py-2.5 border-b-2 text-xs transition-colors whitespace-nowrap">
                Guru Pengampu ({{ $schoolClass->teachers->count() }})
            </button>
            <button type="button"
                    @click="tab = 'schedules'"
                    :class="tab === 'schedules' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary hover:text-ink'"
                    class="px-4 py-2.5 border-b-2 text-xs transition-colors whitespace-nowrap">
                Jadwal Pelajaran ({{ $schoolClass->schedules->count() }})
            </button>
            <button type="button"
                    @click="tab = 'attendance'"
                    :class="tab === 'attendance' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary hover:text-ink'"
                    class="px-4 py-2.5 border-b-2 text-xs transition-colors whitespace-nowrap">
                Rekap Absensi
            </button>
        </div>

        <!-- Tab 1: Daftar Siswa -->
        <div x-show="tab === 'students'" class="space-y-3">
            <x-card>
                <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
                    <h3 class="text-sm font-semibold text-ink">Daftar Siswa Rombel {{ $schoolClass->name }}</h3>
                    <a href="{{ route('students.index', ['class_id' => $schoolClass->id, 'export' => 'xlsx']) }}"
                       class="edudash-btn-secondary text-xs h-8 px-2.5 gap-1.5">
                        <x-heroicon-o-table-cells class="size-3.5" />
                        <span>Ekspor Excel</span>
                    </a>
                </div>

                <div class="divide-y divide-line">
                    @forelse ($schoolClass->students as $idx => $st)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="font-mono text-muted tabular-nums w-6 text-right">{{ $idx + 1 }}</span>
                                <x-avatar :name="$st->name" :src="$st->img_url" size="sm" />
                                <div class="min-w-0">
                                    <p class="font-medium text-ink truncate">{{ $st->name }}</p>
                                    <p class="font-mono text-[11px] text-muted">{{ $st->student_code }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$st->status" />
                                <button type="button"
                                        onclick="window.openDrawer({
                                            title: '{{ addslashes($st->name) }}',
                                            subtitle: '{{ $st->student_code }} · {{ $schoolClass->name }}',
                                            badge: '{{ $st->status }}',
                                            meta: [
                                                { label: 'NISN / Kode', value: '{{ $st->student_code }}' },
                                                { label: 'Kelas Rombel', value: '{{ $schoolClass->name }}' },
                                                { label: 'Email Akun', value: '{{ $st->user?->email ?? '–' }}' },
                                                { label: 'Telepon', value: '{{ $st->user?->phone ?? '–' }}' },
                                                { label: 'Terdaftar Sejak', value: '{{ format_date($st->created_at) }}' }
                                            ],
                                            actions: [
                                                { label: 'Tutup', url: 'javascript:void(0)', class: 'edudash-btn-secondary text-xs h-8 px-3' }
                                            ]
                                        })"
                                        class="edudash-btn-secondary text-xs h-7 px-2">
                                    Detail
                                </button>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Belum ada siswa di kelas ini" />
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Tab 2: Guru Pengampu -->
        <div x-show="tab === 'teachers'" style="display: none;" class="space-y-3">
            <x-card>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    @forelse ($schoolClass->teachers as $tch)
                        <div class="p-3.5 rounded-xl border border-line bg-sidebar flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-avatar :name="$tch->name" :src="$tch->img_url" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink truncate">{{ $tch->name }}</p>
                                    <p class="text-xs text-primary font-medium mt-0.5">{{ $tch->subject?->name ?? 'Mata Pelajaran Umum' }}</p>
                                    <p class="text-[11px] text-muted">{{ $tch->phone ?: '–' }}</p>
                                </div>
                            </div>
                            <span class="edudash-keycap">{{ $tch->teacher_code }}</span>
                        </div>
                    @empty
                        <div class="col-span-2">
                            <x-empty-state title="Belum ada guru pengampu yang ditugaskan ke kelas ini" />
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Tab 3: Jadwal Pelajaran -->
        <div x-show="tab === 'schedules'" style="display: none;" class="space-y-3">
            <x-card>
                @php
                    $groupedSchedules = $schoolClass->schedules->groupBy('day_of_week');
                @endphp
                <div class="space-y-4">
                    @forelse (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dName)
                        @if ($groupedSchedules->has($dName))
                            <div>
                                <h4 class="text-xs font-bold text-ink uppercase tracking-wider mb-2 flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-primary"></span>
                                    <span>{{ $dName }}</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach ($groupedSchedules->get($dName) as $sch)
                                        <div class="p-3 rounded-xl border border-line bg-sidebar text-xs">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="edudash-keycap">{{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}</span>
                                                @if ($sch->isOngoingNow())
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                                @endif
                                            </div>
                                            <p class="font-medium text-ink">{{ $sch->subject?->name }}</p>
                                            <p class="text-muted mt-0.5">{{ $sch->teacher?->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @empty
                        <x-empty-state title="Belum ada jadwal untuk kelas ini" />
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Tab 4: Rekap Absensi -->
        <div x-show="tab === 'attendance'" style="display: none;" class="space-y-3">
            <x-card>
                <h3 class="text-sm font-semibold text-ink mb-3">Rekapitulasi Kehadiran Hari Ini ({{ format_date(now()) }})</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center mb-4">
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                        <span class="text-xs text-emerald-700 font-medium">Hadir</span>
                        <p class="text-xl font-bold text-emerald-900 mt-1 font-mono">{{ $attendanceSummary['Hadir'] }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-sky-50 border border-sky-200">
                        <span class="text-xs text-sky-700 font-medium">Izin</span>
                        <p class="text-xl font-bold text-sky-900 mt-1 font-mono">{{ $attendanceSummary['Izin'] }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                        <span class="text-xs text-amber-700 font-medium">Sakit</span>
                        <p class="text-xl font-bold text-amber-900 mt-1 font-mono">{{ $attendanceSummary['Sakit'] }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200">
                        <span class="text-xs text-rose-700 font-medium">Alpa</span>
                        <p class="text-xl font-bold text-rose-900 mt-1 font-mono">{{ $attendanceSummary['Alpa'] }}</p>
                    </div>
                </div>

                @if (!$user->isStudent())
                    <div class="pt-3 border-t border-line flex justify-end">
                        <a href="{{ route('attendances.index', ['class_id' => $schoolClass->id]) }}" wire:navigate
                           class="edudash-btn-primary text-xs h-9 px-3.5">
                            Buka Lembar Absensi Lengkap
                        </a>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
