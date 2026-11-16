<x-layouts.app title="Mata Pelajaran" :breadcrumbs="[['label' => 'Mata Pelajaran', 'url' => route('subjects.index')], ['label' => 'Daftar Mata Pelajaran']]">
    @php $user = auth()->user(); @endphp

    <x-page-header
        title="Mata Pelajaran"
        subtitle="Kelola kurikulum mata pelajaran, bobot jam pelajaran, dan jumlah pengajar"
        :ctaLabel="$user->isAdmin() ? 'Tambah Mapel' : null"
        :ctaUrl="$user->isAdmin() ? route('subjects.create') : null">
        <x-slot:actions>
            @if ($user->isAdmin())
                <a href="{{ route('archive.index', ['tab' => 'subjects']) }}" wire:navigate
                   class="edudash-btn-secondary gap-2 text-xs">
                    <x-heroicon-o-archive-box class="size-4 stroke-[1.5] text-muted" />
                    <span>Arsip ({{ format_number($archivedCount) }})</span>
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <livewire:subjects-table />
</x-layouts.app>
