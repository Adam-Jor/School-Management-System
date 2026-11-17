<x-layouts.app title="Absensi Siswa" :breadcrumbs="[['label' => 'Absensi Harian & Rekap Bulanan']]">
    @php $user = auth()->user(); @endphp

    <x-page-header
        title="Absensi Siswa"
        :subtitle="$user->isTeacher()
            ? 'Kelola absensi harian siswa pada kelas yang sedang Anda ajar dan pantau rekapitulasi kehadiran bulanan.'
            : ($user->isStudent()
                ? 'Pantau catatan kehadiran harian dan rekapitulasi absensi pribadi Anda.'
                : 'Kelola absensi harian siswa per kelas dan pantau rekapitulasi kehadiran bulanan.')" />

    <!-- Filter Kelas, Tanggal Harian, & Bulan Rekap -->
    <x-card class="mb-4 sm:mb-6">
        <form action="{{ route('attendances.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-ink mb-1.5">
                    {{ $user->isTeacher() ? 'Kelas yang Anda Ajar' : 'Pilih Kelas' }}
                </label>
                <select name="class_id" onchange="this.form.submit()" class="edudash-select h-10 text-xs" @disabled($classes->isEmpty())>
                    @forelse ($classes as $cls)
                        <option value="{{ $cls->id }}" @selected($selectedClassId === $cls->id)>
                            {{ $cls->name }} ({{ $cls->code }})
                        </option>
                    @empty
                        <option value="">Belum ada kelas ditugaskan</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-ink mb-1.5">Tanggal Absensi Harian</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="edudash-input h-10 text-xs" />
            </div>

            <div>
                <label class="block text-xs font-medium text-ink mb-1.5">Bulan Rekapitulasi</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="edudash-input h-10 text-xs" />
            </div>

            <div>
                <button type="submit" class="edudash-btn-secondary w-full h-10 text-xs">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </x-card>

    @if ($errors->any())
        <div class="mb-4 sm:mb-6 px-4 py-3 rounded-xl bg-error-soft/40 border border-error/25 text-xs text-error space-y-1">
            @foreach ($errors->all() as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
        <!-- Kiri (col-span-7): Form Input Absensi Harian per Kelas (Mobile-First Cards + Desktop Table) -->
        <x-card class="lg:col-span-7"
                x-data="{
                    markAllPresent() {
                        this.$el.querySelectorAll('select[data-attendance-select]').forEach(sel => {
                            sel.value = 'Hadir';
                            sel.dispatchEvent(new Event('change'));
                        });
                    }
                }">
            <div class="flex flex-wrap items-center justify-between gap-2.5 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Absensi Harian Kelas</h2>
                    <p class="text-xs text-muted mt-0.5">Tanggal: {{ format_date($selectedDate) }} · Total {{ format_number(count($students)) }} Siswa</p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($canRecordAttendance && count($students) > 0)
                        <button type="button"
                                @click="markAllPresent()"
                                class="h-8 px-3 rounded-lg bg-success-soft text-[#15803D] text-xs font-medium hover:opacity-90 active:scale-95 transition">
                            Tandai Semua Hadir
                        </button>
                    @endif
                    <span class="px-3 py-1 rounded-md bg-raised border border-line font-mono text-secondary text-xs font-semibold">
                        {{ $classes->firstWhere('id', $selectedClassId)?->name ?? '-' }}
                    </span>
                </div>
            </div>

            <form action="{{ route('attendances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                <input type="hidden" name="date" value="{{ $selectedDate }}" />

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse block md:table">
                        <thead class="hidden md:table-header-group">
                            <tr class="border-b border-line text-xs font-medium text-muted">
                                <th class="py-3 pl-2 text-right w-10">No.</th>
                                <th class="py-3 px-3">Siswa</th>
                                <th class="py-3 px-3">Status Kehadiran</th>
                                <th class="py-3 pr-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="block md:table-row-group space-y-3 md:space-y-0 md:divide-y md:divide-line text-sm">
                            @forelse ($students as $index => $student)
                                @php
                                    $rec = $dailyAttendances->get($student->id);
                                    $currentStatus = old("statuses.{$student->id}", $rec?->status ?? 'Hadir');
                                    $currentNotes = old("notes.{$student->id}", $rec?->notes);
                                @endphp
                                <tr x-data="{ st: '{{ $currentStatus }}' }"
                                    class="block md:table-row rounded-xl md:rounded-none border border-line md:border-none bg-sidebar md:bg-transparent p-3.5 md:p-0 hover:bg-page/50">
                                    <td class="hidden md:table-cell py-3 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                        {{ format_number($index + 1) }}
                                    </td>
                                    <td class="block md:table-cell md:py-3 md:px-3">
                                        <div class="flex items-center justify-between md:block">
                                            <div>
                                                <p class="text-[13px] font-semibold text-ink">{{ $student->name }}</p>
                                                <p class="text-[11px] font-mono text-muted mt-0.5">{{ $student->student_code }}</p>
                                            </div>
                                            <span class="md:hidden edudash-keycap">#{{ format_number($index + 1) }}</span>
                                        </div>
                                    </td>
                                    <td class="block md:table-cell mt-3 md:mt-0 md:py-3 md:px-3">
                                        @if ($canRecordAttendance)
                                            <!-- Mobile Touch Pill Selector (< md) synced to select -->
                                            <div class="grid grid-cols-4 gap-1.5 md:hidden mb-2">
                                                @foreach (['Hadir', 'Izin', 'Sakit', 'Alpa'] as $stOption)
                                                    <button type="button"
                                                            @click="st = '{{ $stOption }}'"
                                                            :class="st === '{{ $stOption }}'
                                                                ? 'bg-ink text-white border-ink font-semibold'
                                                                : 'bg-surface text-secondary border-line'"
                                                            class="h-9 rounded-lg border text-xs transition-colors duration-150">
                                                        {{ $stOption }}
                                                    </button>
                                                @endforeach
                                            </div>

                                            <!-- Single Select Element for Form Submission & Desktop -->
                                            <select name="statuses[{{ $student->id }}]"
                                                    data-attendance-select
                                                    x-model="st"
                                                    class="hidden md:inline-block h-8 px-3 rounded-lg bg-sidebar border border-line text-xs font-medium text-ink">
                                                @foreach (['Hadir', 'Izin', 'Sakit', 'Alpa'] as $stOption)
                                                    <option value="{{ $stOption }}" @selected($currentStatus === $stOption)>{{ $stOption }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <x-status-badge :status="$currentStatus" />
                                        @endif
                                    </td>
                                    <td class="block md:table-cell mt-2 md:mt-0 md:py-3 md:pr-2">
                                        @if ($canRecordAttendance)
                                            <input type="text"
                                                   name="notes[{{ $student->id }}]"
                                                   value="{{ $currentNotes }}"
                                                   placeholder="Catatan keterangan (opsional)…"
                                                   class="h-9 md:h-8 px-3 rounded-lg bg-surface md:bg-sidebar border border-line text-xs text-ink w-full" />
                                        @else
                                            <span class="text-xs text-muted">{{ $rec?->notes ?: '–' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="block md:table-row">
                                    <td colspan="4" class="block md:table-cell">
                                        <x-empty-state
                                            :title="$user->isTeacher() && $classes->isEmpty() ? 'Belum ada penugasan kelas mengajar' : 'Belum ada siswa di kelas ini'"
                                            :description="$user->isTeacher() && $classes->isEmpty() ? 'Hubungi administrator sekolah untuk menetapkan kelas yang Anda ajar.' : null" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($canRecordAttendance && count($students) > 0)
                    <div class="mt-5 pt-4 border-t border-line flex justify-end">
                        <button type="submit" class="edudash-btn-primary text-xs w-full sm:w-auto h-11 sm:h-10">
                            Simpan Absensi Harian
                        </button>
                    </div>
                @endif
            </form>
        </x-card>

        <!-- Kanan (col-span-5): Rekap Absensi Bulanan -->
        <x-card class="lg:col-span-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Rekap Absensi Bulanan</h2>
                    <p class="text-xs text-muted mt-0.5">Periode: {{ $selectedMonth }}</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-success-soft text-[#15803D] text-xs font-semibold">
                    Rekapitulasi
                </span>
            </div>

            <!-- Mobile Monthly Summary Cards (< sm) -->
            <div class="sm:hidden space-y-2.5">
                @foreach ($students as $student)
                    @php $sum = $monthlySummary[$student->id] ?? ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0]; @endphp
                    <div class="rounded-xl border border-line bg-sidebar p-3">
                        <p class="text-xs font-semibold text-ink mb-2">{{ $student->name }}</p>
                        <div class="grid grid-cols-4 gap-1.5 text-center text-[11px]">
                            <div class="rounded-lg bg-surface border border-line py-1.5">
                                <span class="block text-muted">Hadir</span>
                                <span class="font-mono font-bold text-[#15803D] tabular-nums">{{ format_number($sum['Hadir']) }}</span>
                            </div>
                            <div class="rounded-lg bg-surface border border-line py-1.5">
                                <span class="block text-muted">Izin</span>
                                <span class="font-mono font-semibold text-[#B45309] tabular-nums">{{ format_number($sum['Izin']) }}</span>
                            </div>
                            <div class="rounded-lg bg-surface border border-line py-1.5">
                                <span class="block text-muted">Sakit</span>
                                <span class="font-mono font-semibold text-primary tabular-nums">{{ format_number($sum['Sakit']) }}</span>
                            </div>
                            <div class="rounded-lg bg-surface border border-line py-1.5">
                                <span class="block text-muted">Alpa</span>
                                <span class="font-mono font-semibold text-[#B91C1C] tabular-nums">{{ format_number($sum['Alpa']) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop / Tablet Table (sm+) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-medium text-muted">
                            <th class="py-2.5 pl-2">Nama Siswa</th>
                            <th class="py-2.5 px-2 text-right text-[#15803D]">Hadir</th>
                            <th class="py-2.5 px-2 text-right text-[#B45309]">Izin</th>
                            <th class="py-2.5 px-2 text-right text-primary">Sakit</th>
                            <th class="py-2.5 pr-2 text-right text-[#B91C1C]">Alpa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-xs">
                        @foreach ($students as $student)
                            @php $sum = $monthlySummary[$student->id] ?? ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0]; @endphp
                            <tr class="hover:bg-page/50">
                                <td class="py-3 pl-2 font-semibold text-ink">{{ $student->name }}</td>
                                <td class="py-3 px-2 text-right font-bold text-[#15803D] tabular-nums">{{ format_number($sum['Hadir']) }}</td>
                                <td class="py-3 px-2 text-right font-semibold text-[#B45309] tabular-nums">{{ format_number($sum['Izin']) }}</td>
                                <td class="py-3 px-2 text-right font-semibold text-primary tabular-nums">{{ format_number($sum['Sakit']) }}</td>
                                <td class="py-3 pr-2 text-right font-semibold text-[#B91C1C] tabular-nums">{{ format_number($sum['Alpa']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
