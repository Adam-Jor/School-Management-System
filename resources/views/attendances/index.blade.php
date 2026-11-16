<x-layouts.app title="Absensi Siswa" :breadcrumbs="[['label' => 'Absensi Harian & Rekap Bulanan']]">
    @php $user = auth()->user(); @endphp

    <x-page-header
        title="Absensi Siswa"
        subtitle="Kelola absensi harian siswa per kelas (tbl_attendances) dan pantau rekapitulasi kehadiran bulanan." />

    <!-- Filter Kelas, Tanggal Harian, & Bulan Rekap -->
    <x-card class="mb-6">
        <form action="{{ route('attendances.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-ink mb-1.5">Pilih Kelas</label>
                <select name="class_id" onchange="this.form.submit()" class="edudash-select h-10 text-xs">
                    @foreach ($classes as $cls)
                        <option value="{{ $cls->id }}" @selected($selectedClassId === $cls->id)>
                            {{ $cls->name }} ({{ $cls->code }})
                        </option>
                    @endforeach
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kiri (col-span-7): Form Input Absensi Harian per Kelas -->
        <x-card class="lg:col-span-7">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Absensi Harian Kelas</h2>
                    <p class="text-xs text-muted mt-0.5">Tanggal: {{ format_date($selectedDate) }} · Total {{ format_number(count($students)) }} Siswa</p>
                </div>
                <span class="px-3 py-1 rounded-md bg-raised border border-line font-mono text-secondary text-xs font-semibold">
                    {{ $classes->firstWhere('id', $selectedClassId)?->name ?? '-' }}
                </span>
            </div>

            <form action="{{ route('attendances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                <input type="hidden" name="date" value="{{ $selectedDate }}" />

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-line text-xs font-medium text-muted">
                                <th class="py-3 pl-2 text-right w-10">No.</th>
                                <th class="py-3 px-3">Siswa</th>
                                <th class="py-3 px-3">Status Kehadiran</th>
                                <th class="py-3 pr-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @forelse ($students as $index => $student)
                                @php
                                    $rec = $dailyAttendances->get($student->id);
                                    $currentStatus = $rec?->status ?? 'Hadir';
                                @endphp
                                <tr class="hover:bg-page/50">
                                    <td class="py-3 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                        {{ format_number($index + 1) }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <p class="text-[13px] font-semibold text-ink">{{ $student->name }}</p>
                                        <p class="text-[11px] text-muted">{{ $student->student_code }}</p>
                                    </td>
                                    <td class="py-3 px-3">
                                        @if ($user->isAdmin())
                                            <select name="statuses[{{ $student->id }}]" class="h-8 px-3 rounded-lg bg-sidebar border border-line text-xs font-medium text-ink">
                                                @foreach (['Hadir', 'Izin', 'Sakit', 'Alpa'] as $st)
                                                    <option value="{{ $st }}" @selected($currentStatus === $st)>{{ $st }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <x-status-badge :status="$currentStatus" />
                                        @endif
                                    </td>
                                    <td class="py-3 pr-2">
                                        @if ($user->isAdmin())
                                            <input type="text"
                                                   name="notes[{{ $student->id }}]"
                                                   value="{{ $rec?->notes }}"
                                                   placeholder="Catatan opsional…"
                                                   class="h-8 px-3 rounded-lg bg-sidebar border border-line text-xs text-ink w-full" />
                                        @else
                                            <span class="text-xs text-muted">{{ $rec?->notes ?: '–' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-empty-state title="Belum ada siswa di kelas ini" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($user->isAdmin() && count($students) > 0)
                    <div class="mt-5 pt-4 border-t border-line flex justify-end">
                        <button type="submit" class="edudash-btn-primary text-xs">
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

            <div class="overflow-x-auto">
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
