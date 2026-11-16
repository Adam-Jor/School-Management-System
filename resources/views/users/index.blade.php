<x-layouts.app title="Data Pengguna" :breadcrumbs="[['label' => 'Data Pengguna', 'url' => route('users.index')], ['label' => 'Daftar Akun Sistem']]">
    <x-page-header
        title="Data Pengguna"
        subtitle="Kelola seluruh akun pengguna beserta hak akses masing-masing peran (Admin, Teacher, Student)"
        ctaLabel="Tambah Pengguna"
        :ctaUrl="route('users.create')" />

    <livewire:users-table />
</x-layouts.app>
