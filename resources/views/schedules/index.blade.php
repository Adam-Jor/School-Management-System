<x-layouts.app title="Jadwal Pelajaran" :breadcrumbs="[['label' => 'Jadwal Pelajaran']]">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <x-page-header
            title="Jadwal Pelajaran Mingguan"
            subtitle="Matriks alokasi waktu kegiatan belajar-mengajar per rombongan belajar dan pengajar" />

        @if (auth()->user()?->isAdmin())
            <button type="button"
                    onclick="document.getElementById('add_schedule_modal').showModal()"
                    class="edudash-btn-primary text-xs h-9 px-3 gap-1.5 self-start sm:self-auto">
                <x-heroicon-o-plus class="size-4" />
                <span>Tambah Jadwal</span>
            </button>
        @endif
    </div>

    <!-- Filter & Day Navigation -->
    <x-card class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Day Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('schedules.index', ['class_id' => $selectedClassId]) }}" wire:navigate
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ empty($selectedDay) ? 'bg-ink text-white' : 'bg-sidebar text-secondary hover:text-ink hover:bg-raised' }}">
                    Semua Hari
                </a>
                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                    <a href="{{ route('schedules.index', ['class_id' => $selectedClassId, 'day' => $d]) }}" wire:navigate
                       class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ $selectedDay === $d ? 'bg-ink text-white' : 'bg-sidebar text-secondary hover:text-ink hover:bg-raised' }}">
                        {{ $d }}
                    </a>
                @endforeach
            </div>

            <!-- Class Filter (For Admin & Teacher) -->
            @if (!$isStudent)
                <form action="{{ route('schedules.index') }}" method="GET" class="flex items-center gap-2">
                    @if ($selectedDay)
                        <input type="hidden" name="day" value="{{ $selectedDay }}">
                    @endif
                    <select name="class_id"
                            onchange="this.form.requestSubmit()"
                            class="edudash-select text-xs h-9 w-44">
                        <option value="">Semua Rombel ({{ $classes->count() }})</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}" @selected($selectedClassId == $cls->id)>{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-card>

    <!-- Schedule Cards Grid by Day -->
    @php
        $daysToShow = $selectedDay ? [$selectedDay] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hasAny = false;
    @endphp

    <div class="space-y-6">
        @foreach ($daysToShow as $dayName)
            @php
                $daySchedules = $schedules->get($dayName, collect());
                if ($daySchedules->count() > 0) $hasAny = true;
            @endphp

            @if ($daySchedules->count() > 0 || $selectedDay)
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-line">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-primary"></span>
                            <h3 class="text-sm font-semibold text-ink">{{ $dayName }}</h3>
                        </div>
                        <span class="text-xs text-muted font-mono tabular-nums">{{ $daySchedules->count() }} sesi</span>
                    </div>

                    @if ($daySchedules->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            @foreach ($daySchedules as $item)
                                @php
                                    $isOngoing = $item->isOngoingNow();
                                @endphp
                                <div class="rounded-xl border {{ $isOngoing ? 'border-primary ring-2 ring-primary/10 bg-primary-soft/10' : 'border-line bg-surface' }} p-4 flex flex-col justify-between transition-shadow hover:shadow-md">
                                    <div>
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="edudash-keycap">{{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}</span>
                                                @if ($isOngoing)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 animate-pulse">Sedang Berlangsung</span>
                                                @endif
                                            </div>
                                            <span class="text-xs font-semibold text-primary">{{ $item->schoolClass?->name }}</span>
                                        </div>

                                        <h4 class="text-sm font-medium text-ink">{{ $item->subject?->name }}</h4>
                                        <p class="text-xs text-secondary mt-1 flex items-center gap-1.5">
                                            <x-heroicon-o-user class="size-3.5 text-muted" />
                                            <span>{{ $item->teacher?->name ?? 'Belum ditentukan' }}</span>
                                        </p>
                                        @if ($item->room)
                                            <p class="text-[11px] text-muted mt-1 flex items-center gap-1.5">
                                                <x-heroicon-o-building-office class="size-3.5 text-muted" />
                                                <span>Ruang: {{ $item->room }}</span>
                                            </p>
                                        @endif
                                    </div>

                                    @if (auth()->user()?->isAdmin())
                                        <div class="pt-3 mt-3 border-t border-line/60 flex items-center justify-end">
                                            <form action="{{ route('schedules.archive', $item) }}" method="POST" onsubmit="return confirm('Arsipkan jadwal ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 rounded-xl border border-line bg-sidebar text-center text-xs text-muted">
                            Tidak ada jadwal pelajaran pada hari {{ $dayName }}.
                        </div>
                    @endif
                </div>
            @endif
        @endforeach

        @if (!$hasAny)
            <x-card>
                <x-empty-state
                    title="Jadwal Pelajaran Kosong"
                    description="Belum ada data jadwal pelajaran yang didaftarkan untuk filter ini" />
            </x-card>
        @endif
    </div>

    <!-- Modal Tambah Jadwal (Admin) -->
    @if (auth()->user()?->isAdmin())
        <dialog id="add_schedule_modal" class="modal">
            <div class="modal-box bg-surface border border-line rounded-2xl max-w-lg">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3 text-muted">✕</button>
                </form>
                <h3 class="font-bold text-base text-ink mb-4">Tambah Jadwal Pelajaran Baru</h3>

                <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="class_id" label="Kelas Rombel" required="true" placeholder="-- Pilih Kelas --">
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" @selected(old('class_id', $selectedClassId) == $cls->id)>{{ $cls->name }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-select name="subject_id" label="Mata Pelajaran" required="true" placeholder="-- Pilih Mapel --">
                            @foreach ($subjects as $sbj)
                                <option value="{{ $sbj->id }}" @selected(old('subject_id') == $sbj->id)>{{ $sbj->name }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-select name="teacher_id" label="Guru Pengajar" required="true" placeholder="-- Pilih Guru --">
                            @foreach ($teachers as $tch)
                                <option value="{{ $tch->id }}" @selected(old('teacher_id') == $tch->id)>{{ $tch->name }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-select name="day_of_week" label="Hari" required="true" placeholder="-- Pilih Hari --">
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                <option value="{{ $d }}" @selected(old('day_of_week', $selectedDay) == $d)>{{ $d }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-input type="time" name="start_time" label="Jam Mulai" required="true" placeholder="07:30" />
                        <x-form-input type="time" name="end_time" label="Jam Selesai" required="true" placeholder="09:00" />
                    </div>

                    <x-form-input type="text" name="room" label="Ruang Kelas / Lab" placeholder="Contoh: Lab Komputer 1 / Ruang 10-A" />

                    <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                        <button type="button" onclick="document.getElementById('add_schedule_modal').close()" class="edudash-btn-secondary text-xs h-9 px-3">Batal</button>
                        <button type="submit" class="edudash-btn-primary text-xs h-9 px-4">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    @endif
</x-layouts.app>
