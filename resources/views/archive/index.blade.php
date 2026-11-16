<x-layouts.app title="Archive / Trash" :breadcrumbs="[['label' => 'Archive & Pemulihan Data']]">
    <x-page-header
        title="Arsip & Pemulihan Data"
        subtitle="Lihat dan pulihkan data yang telah diarsipkan tanpa kehilangan riwayat akademik." />

    <!-- Tab Selector Pill -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        @php
            $tabs = [
                'students' => ['label' => 'Siswa', 'count' => count($archivedStudents)],
                'teachers' => ['label' => 'Guru', 'count' => count($archivedTeachers)],
                'classes' => ['label' => 'Kelas', 'count' => count($archivedClasses)],
                'subjects' => ['label' => 'Mata Pelajaran', 'count' => count($archivedSubjects)],
                'users' => ['label' => 'Pengguna', 'count' => count($archivedUsers)],
            ];
        @endphp
        @foreach ($tabs as $key => $info)
            <a href="{{ route('archive.index', ['tab' => $key]) }}" wire:navigate
               class="px-4 py-2 rounded-full text-xs font-semibold transition {{ $tab === $key ? 'bg-ink text-white' : 'bg-surface border border-line text-muted hover:text-ink' }}">
                {{ $info['label'] }} ({{ format_number($info['count']) }})
            </a>
        @endforeach
    </div>

    <x-card>
        @php
            $records = match($tab) {
                'teachers' => $archivedTeachers,
                'classes' => $archivedClasses,
                'subjects' => $archivedSubjects,
                'users' => $archivedUsers,
                default => $archivedStudents,
            };
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-line text-xs font-medium text-muted">
                        <th class="py-3 pl-2 text-right w-12">No.</th>
                        <th class="py-3 px-3">Kode / ID</th>
                        <th class="py-3 px-3">Nama Data</th>
                        <th class="py-3 px-3">Di-archive Pada</th>
                        <th class="py-3 pr-2 text-right">Aksi Pemulihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-sm">
                    @forelse ($records as $index => $rec)
                        <tr class="hover:bg-page/60">
                            <td class="py-3.5 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                {{ format_number($index + 1) }}
                            </td>
                            <td class="py-3.5 px-3 text-xs font-mono text-muted">
                                {{ $rec->student_code ?? $rec->teacher_code ?? $rec->code ?? $rec->username ?? '#' . $rec->id }}
                            </td>
                            <td class="py-3.5 px-3 text-[13px] font-semibold text-ink">
                                {{ $rec->name }}
                            </td>
                            <td class="py-3.5 px-3 text-xs text-muted">
                                {{ format_date($rec->updated_at, true) }}
                            </td>
                            <td class="py-3.5 pr-2 text-right">
                                <form action="{{ route('archive.restore', ['type' => $tab, 'id' => $rec->id]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="px-4 py-1.5 rounded-full bg-success-soft text-[#15803D] text-xs font-semibold hover:bg-success hover:text-white transition">
                                        Restore Data
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state
                                    title="Tidak Ada Data di Archive"
                                    description="Seluruh data pada modul ini masih berstatus aktif (archived = 0)." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
