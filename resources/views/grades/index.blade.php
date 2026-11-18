<x-layouts.app title="Nilai & Rapor Siswa" :breadcrumbs="[['label' => 'Nilai Akademik & Cetak Rapor PDF']]">
    @php $user = auth()->user(); @endphp

    <x-page-header
        title="Nilai & Rapor Akademik"
        :subtitle="$user->isStudent()
            ? 'Lihat rekap nilai akademik pribadi dan unduh rapor PDF resmi Anda.'
            : 'Kelola nilai siswa per mata pelajaran dan cetak rapor PDF per siswa.'" />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
        <!-- Form Input Nilai (Admin & Teacher) -->
        @if ($user->isAdmin() || $user->isTeacher())
            <x-card class="lg:col-span-4">
                <h2 class="text-base font-semibold text-ink mb-1">Input / Perbarui Nilai</h2>
                <p class="text-xs text-muted mb-4">Predikat (A/B/C/D) dihitung otomatis dari nilai angka.</p>

                <form action="{{ route('grades.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Pilih Siswa <span class="text-error font-bold">*</span></label>
                        <select name="student_id" required autofocus class="edudash-select text-xs">
                            <option value="">Pilih siswa…</option>
                            @foreach ($students as $st)
                                <option value="{{ $st->id }}" @selected(old('student_id') == $st->id)>
                                    {{ $st->student_code }} — {{ $st->name }} ({{ $st->schoolClass?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id') <p class="text-[11px] font-medium text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Mata Pelajaran <span class="text-error font-bold">*</span></label>
                        <select name="subject_id" required class="edudash-select text-xs">
                            <option value="">Pilih mata pelajaran…</option>
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}" @selected(old('subject_id') == $sub->id)>{{ $sub->code }} — {{ $sub->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-[11px] font-medium text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Semester <span class="text-error font-bold">*</span></label>
                        <select name="semester" required class="edudash-select text-xs">
                            <option value="Ganjil 2026/2027" @selected(old('semester', 'Ganjil 2026/2027') === 'Ganjil 2026/2027')>Ganjil 2026/2027</option>
                            <option value="Genap 2026/2027" @selected(old('semester') === 'Genap 2026/2027')>Genap 2026/2027</option>
                        </select>
                        @error('semester') <p class="text-[11px] font-medium text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Nilai Akhir (0–100) <span class="text-error font-bold">*</span></label>
                        <input type="number" name="score" value="{{ old('score') }}" min="0" max="100" step="0.01" placeholder="Contoh: 92" required class="edudash-input text-xs" />
                        @error('score') <p class="text-[11px] font-medium text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="edudash-btn-primary w-full text-xs">
                        Simpan Nilai Siswa
                    </button>
                </form>
            </x-card>
        @endif

        <!-- Daftar Nilai & Tombol Cetak Rapor PDF -->
        <x-card class="{{ ($user->isAdmin() || $user->isTeacher()) ? 'lg:col-span-8' : 'lg:col-span-12' }}">
            <form action="{{ route('grades.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div class="grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-2.5 w-full sm:w-auto">
                    @unless ($user->isStudent())
                        <select name="class_id" onchange="this.form.submit()" class="h-10 sm:h-9 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-medium text-ink w-full sm:w-auto">
                            <option value="">Semua Kelas</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" @selected((string) $classId === (string) $cls->id)>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    @endunless

                    <select name="subject_id" onchange="this.form.submit()" class="h-10 sm:h-9 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-medium text-ink w-full sm:w-auto">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach ($subjects as $sub)
                            <option value="{{ $sub->id }}" @selected((string) $subjectId === (string) $sub->id)>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <span class="px-3.5 py-1.5 rounded-md bg-raised border border-line font-mono text-secondary text-xs font-semibold self-start sm:self-auto">
                    Total: {{ format_number($grades->total()) }} Data Nilai
                </span>
            </form>

            <!-- Mobile-First Grade Cards (< md) -->
            <div class="md:hidden space-y-3">
                @forelse ($grades as $idx => $grade)
                    <div class="rounded-xl border border-line bg-sidebar p-3.5 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-ink truncate">{{ $grade->student?->name ?? '-' }}</p>
                                <p class="text-xs text-muted mt-0.5">{{ $grade->student?->student_code }} · {{ $grade->student?->schoolClass?->name ?? '-' }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="font-mono text-lg font-bold text-ink tabular-nums">{{ format_number($grade->score) }}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-success-soft text-[#15803D] text-xs font-bold">
                                    {{ $grade->predicate }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-line flex items-center justify-between gap-2 text-xs">
                            <div class="min-w-0">
                                <p class="font-medium text-ink truncate">{{ $grade->subject?->code }} · {{ $grade->subject?->name }}</p>
                                <p class="text-muted text-[11px]">{{ $grade->semester }}</p>
                            </div>
                            @if ($grade->student)
                                <a href="{{ route('grades.report.pdf', $grade->student) }}"
                                   class="h-8 px-3 rounded-lg bg-surface border border-line font-mono text-ink text-xs font-semibold hover:bg-primary hover:text-white transition inline-flex items-center gap-1.5 shrink-0">
                                    <x-heroicon-o-document-arrow-down class="size-3.5" />
                                    <span>Rapor PDF</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Belum ada data nilai" />
                @endforelse
            </div>

            <!-- Desktop Table (md+) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-line text-xs font-medium text-muted">
                            <th class="py-3 pl-2 text-right w-10">No.</th>
                            <th class="py-3 px-3">Siswa & Kelas</th>
                            <th class="py-3 px-3">Mata Pelajaran</th>
                            <th class="py-3 px-3">Semester</th>
                            <th class="py-3 px-3 text-right">Nilai Angka</th>
                            <th class="py-3 px-3 text-center">Predikat</th>
                            <th class="py-3 pr-2 text-right">Cetak Rapor PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($grades as $idx => $grade)
                            <tr class="hover:bg-page/60">
                                <td class="py-3.5 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                    {{ format_number(($grades->currentPage() - 1) * $grades->perPage() + $idx + 1) }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <p class="text-[13px] font-semibold text-ink">{{ $grade->student?->name ?? '-' }}</p>
                                    <p class="text-[11px] text-muted">{{ $grade->student?->student_code }} · {{ $grade->student?->schoolClass?->name ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-3 text-xs font-medium text-ink">
                                    {{ $grade->subject?->code }} · {{ $grade->subject?->name }}
                                </td>
                                <td class="py-3.5 px-3 text-xs text-muted">{{ $grade->semester }}</td>
                                <td class="py-3.5 px-3 text-right text-sm font-bold text-ink tabular-nums">
                                    {{ format_number($grade->score) }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full bg-success-soft text-[#15803D] text-xs font-bold">
                                        {{ $grade->predicate }}
                                    </span>
                                </td>
                                <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                    @if ($grade->student)
                                        <a href="{{ route('grades.report.pdf', $grade->student) }}"
                                           class="px-3.5 py-1.5 rounded-md bg-raised border border-line font-mono text-secondary text-xs font-semibold hover:bg-primary hover:text-white transition inline-flex items-center gap-1.5">
                                            <x-heroicon-o-document-arrow-down class="size-3.5" />
                                            <span>Rapor PDF</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="Belum ada data nilai" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-3 border-t border-line flex flex-wrap items-center justify-between gap-2 text-xs text-muted">
                <span>Menampilkan {{ format_number($grades->firstItem() ?? 0) }}–{{ format_number($grades->lastItem() ?? 0) }} dari {{ format_number($grades->total()) }} nilai</span>
                <div>{{ $grades->links() }}</div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
