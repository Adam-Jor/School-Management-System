<x-layouts.app title="Data Kelas" :breadcrumbs="[['label' => 'Data Kelas', 'url' => route('classes.index')], ['label' => 'Daftar Kelas']]">
    @php $user = auth()->user(); @endphp

    <x-page-header
        title="Data Kelas"
        subtitle="Kelola data rombongan belajar, kapasitas kursi, dan jumlah siswa per kelas"
        :ctaLabel="$user->isAdmin() ? 'Tambah Kelas' : null"
        :ctaUrl="$user->isAdmin() ? route('classes.create') : null">
        <x-slot:actions>
            @if ($user->isAdmin())
                <a href="{{ route('archive.index', ['tab' => 'classes']) }}" wire:navigate
                   class="edudash-btn-secondary gap-2 text-xs">
                    <x-heroicon-o-archive-box class="size-4 stroke-[1.5] text-muted" />
                    <span>Arsip ({{ format_number($archivedCount) }})</span>
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <livewire:classes-table />
</x-layouts.app>
