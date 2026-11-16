<x-layouts.app title="Data Kelas" :breadcrumbs="[['label' => 'Data Kelas', 'url' => route('classes.index')], ['label' => 'Daftar Kelas']]">
    @php $user = auth()->user(); @endphp

    <x-page-header
        title="Data Kelas"
        subtitle="Kelola data rombongan belajar (tbl_classes), kapasitas kursi, dan jumlah siswa per kelas."
        :ctaLabel="$user->isAdmin() ? 'Tambah Kelas' : null"
        :ctaUrl="$user->isAdmin() ? route('classes.create') : null">
        <x-slot:actions>
            @if ($user->isAdmin())
                <a href="{{ route('archive.index', ['tab' => 'classes']) }}" wire:navigate
                   class="px-4 py-2.5 rounded-md bg-raised border border-line font-mono text-secondary font-semibold text-xs inline-flex items-center gap-1.5 hover:bg-primary hover:text-white transition">
                    <x-heroicon-o-archive-box class="size-4" />
                    <span>Archive ({{ format_number($archivedCount) }})</span>
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <form action="{{ route('classes.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="relative flex-1 min-w-[240px] max-w-sm">
                <x-heroicon-o-magnifying-glass class="size-4 text-muted absolute left-4 top-3 pointer-events-none" />
                <input type="search"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Cari kode atau nama kelas…"
                       class="w-full h-10 pl-10 pr-4 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
            </div>
            <span class="px-4 py-1.5 rounded-md bg-raised border border-line font-mono text-secondary text-xs font-semibold">
                Total: {{ format_number($classes->total()) }} Kelas
            </span>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-line text-xs font-medium text-muted">
                        <th class="py-3 pl-2 text-right w-12">No.</th>
                        <th class="py-3 px-3">Kode Kelas</th>
                        <th class="py-3 px-3">Nama Kelas</th>
                        <th class="py-3 px-3">Deskripsi Ruang / Peminatan</th>
                        <th class="py-3 px-3 text-right">Kapasitas</th>
                        <th class="py-3 px-3 text-right">Jumlah Siswa</th>
                        <th class="py-3 px-3">Dibuat</th>
                        @if ($user->isAdmin())
                            <th class="py-3 pr-2 text-right">Aksi CRUD</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-sm">
                    @forelse ($classes as $idx => $cls)
                        <tr class="group hover:bg-page/60 transition duration-150">
                            <td class="py-3.5 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                {{ format_number(($classes->currentPage() - 1) * $classes->perPage() + $idx + 1) }}
                            </td>
                            <td class="py-3.5 px-3 text-xs font-mono font-semibold text-primary">
                                {{ $cls->code }}
                            </td>
                            <td class="py-3.5 px-3 text-[13px] font-bold text-ink">
                                {{ $cls->name }}
                            </td>
                            <td class="py-3.5 px-3 text-xs text-muted max-w-md">
                                {{ $cls->description ?: '–' }}
                            </td>
                            <td class="py-3.5 px-3 text-right text-xs font-semibold text-ink tabular-nums">
                                {{ format_number($cls->capacity) }}
                            </td>
                            <td class="py-3.5 px-3 text-right text-xs font-bold text-primary tabular-nums">
                                {{ format_number($cls->students_count) }} Siswa
                            </td>
                            <td class="py-3.5 px-3 text-xs text-muted whitespace-nowrap">
                                {{ format_date($cls->created_at) }}
                            </td>
                            @if ($user->isAdmin())
                                <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('classes.edit', $cls) }}" wire:navigate
                                           class="px-3 py-1 rounded-md bg-raised border border-line font-mono text-secondary text-[11px] font-semibold hover:bg-primary hover:text-white transition">
                                            Edit
                                        </a>
                                        <button type="button"
                                                @click="openArchiveModal('{{ route('classes.archive', $cls) }}', '{{ addslashes($cls->name) }}', '{{ $cls->code }}')"
                                                class="px-3 py-1 rounded-full bg-error-soft text-[#B91C1C] text-[11px] font-semibold hover:bg-error hover:text-white transition">
                                            Archive
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="Data Kelas Tidak Ditemukan" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted">
            <span>Menampilkan {{ format_number($classes->firstItem() ?? 0) }}–{{ format_number($classes->lastItem() ?? 0) }} dari {{ format_number($classes->total()) }} kelas</span>
            <div>{{ $classes->links() }}</div>
        </div>
    </x-card>
</x-layouts.app>
