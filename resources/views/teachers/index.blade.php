<x-layouts.app title="Data Guru" :breadcrumbs="[['label' => 'Data Guru', 'url' => route('teachers.index')], ['label' => 'Daftar Guru Pengajar']]">
    @php
        $user = auth()->user();
    @endphp

    <div x-data="{ importModalOpen: false }">
        <x-page-header
            title="Data Guru"
            subtitle="Direktori guru pengajar, mata pelajaran yang diampu, dan akun pengguna"
            :ctaLabel="$user->isAdmin() ? 'Tambah Guru' : null"
            :ctaUrl="$user->isAdmin() ? route('teachers.create') : null">
            <x-slot:actions>
                @if ($user->isAdmin())
                    <button type="button"
                            @click="importModalOpen = true"
                            class="edudash-btn-secondary gap-2 text-xs">
                        <x-heroicon-o-arrow-up-tray class="size-4 stroke-[1.5] text-muted" />
                        <span>Impor Excel</span>
                    </button>
                    <a href="{{ route('archive.index', ['tab' => 'teachers']) }}" wire:navigate
                       class="edudash-btn-secondary gap-2 text-xs">
                        <x-heroicon-o-archive-box class="size-4 stroke-[1.5] text-muted" />
                        <span>Arsip ({{ format_number($stats['archived']) }})</span>
                    </a>
                @endif
            </x-slot:actions>
        </x-page-header>

        <x-card class="mb-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-4 lg:divide-x lg:divide-line">
                <x-stat-card label="Total guru pengajar" :value="$stats['total']" delta="+2 vs bulan lalu" deltaType="success" />
                <x-stat-card label="Status mengajar aktif" :value="$stats['active']" delta="Aktif semester ini" deltaType="success" />
                <x-stat-card label="Mata pelajaran" :value="$stats['subjects']" delta="Seluruh mapel terisi" deltaType="primary" />
                <x-stat-card label="Rombongan belajar" :value="$stats['classes']" delta="Kelas 10, 11, dan 12" deltaType="muted" />
            </div>
        </x-card>

        <livewire:teachers-table />

        @if ($user->isAdmin())
            <div x-show="importModalOpen"
                 x-transition.opacity.duration.150ms
                 style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 p-4">
                <div @click.outside="importModalOpen = false"
                     class="bg-surface rounded-2xl p-6 max-w-md w-full shadow-xl border border-line">
                    <h3 class="text-base font-medium text-ink">Impor Data Guru (Excel / CSV)</h3>
                    <p class="text-xs text-secondary mt-1 leading-relaxed">
                        Unggah berkas <code class="edudash-keycap">.xlsx</code> atau <code class="edudash-keycap">.csv</code> dengan urutan kolom: <code class="edudash-keycap">teacher_code, name, phone</code>
                    </p>
                    <form action="{{ route('teachers.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <input type="file" name="excel_file" accept=".xlsx,.xls,.csv,.txt" required class="block w-full text-xs text-secondary file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-line file:bg-sidebar file:text-ink file:font-medium" />
                        @error('excel_file') <p class="text-xs text-error">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="importModalOpen = false" class="edudash-btn-secondary h-9 px-4 text-xs">Batal</button>
                            <button type="submit" class="edudash-btn-primary h-9 px-4 text-xs">Impor Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
