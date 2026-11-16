<x-layouts.app title="Data Pengguna" :breadcrumbs="[['label' => 'Data Pengguna', 'url' => route('users.index')], ['label' => 'Daftar Akun Sistem']]">
    <x-page-header
        title="Data Pengguna"
        subtitle="Kelola seluruh akun pengguna pada tbl_users beserta hak akses role (admin, teacher, student)"
        ctaLabel="Tambah Pengguna"
        :ctaUrl="route('users.create')" />

    <livewire:users-table />
</x-layouts.app>
