<x-layouts.app title="Data Pengguna" :breadcrumbs="[['label' => 'Data Pengguna', 'url' => route('users.index')], ['label' => 'Daftar Akun Sistem']]">
    <x-page-header
        title="Data Pengguna"
        subtitle="Kelola seluruh akun pengguna pada tbl_users beserta hak akses role (admin, teacher, student)."
        ctaLabel="Tambah Pengguna"
        :ctaUrl="route('users.create')" />

    <x-card>
        <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative flex-1 min-w-[240px] max-w-sm">
                    <x-heroicon-o-magnifying-glass class="size-4 text-muted absolute left-4 top-3 pointer-events-none" />
                    <input type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari nama, username, atau email…"
                           class="w-full h-10 pl-10 pr-4 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                </div>

                <select name="role"
                        onchange="this.form.submit()"
                        class="h-10 px-4 rounded-lg bg-sidebar border border-line text-xs font-medium text-ink focus:border-primary focus:outline-none">
                    <option value="">Semua Role</option>
                    <option value="admin" @selected($role === 'admin')>Admin</option>
                    <option value="teacher" @selected($role === 'teacher')>Teacher</option>
                    <option value="student" @selected($role === 'student')>Student</option>
                </select>
            </div>
            <span class="px-4 py-1.5 rounded-md bg-raised border border-line font-mono text-secondary text-xs font-semibold">
                Total: {{ format_number($users->total()) }} Pengguna
            </span>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-line text-xs font-medium text-muted">
                        <th class="py-3 pl-2 text-right w-12">No.</th>
                        <th class="py-3 px-3">Pengguna</th>
                        <th class="py-3 px-3">Username</th>
                        <th class="py-3 px-3">Role</th>
                        <th class="py-3 px-3">Login Terakhir</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 pr-2 text-right">Aksi CRUD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-sm">
                    @foreach ($users as $idx => $u)
                        <tr class="group hover:bg-page/60 transition duration-150">
                            <td class="py-3.5 pl-2 text-right text-xs font-semibold text-muted tabular-nums">
                                {{ format_number(($users->currentPage() - 1) * $users->perPage() + $idx + 1) }}
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$u->name" :src="$u->img_url" />
                                    <div>
                                        <p class="text-[13px] font-semibold text-ink">{{ $u->name }}</p>
                                        <p class="text-[11px] text-muted">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-xs font-mono text-muted">{{ $u->username }}</td>
                            <td class="py-3.5 px-3">
                                <span class="px-3 py-1 rounded-md bg-raised border border-line font-mono text-secondary text-[11px] font-semibold uppercase">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-xs text-muted whitespace-nowrap">
                                {{ format_date($u->last_login_time, true) }}
                            </td>
                            <td class="py-3.5 px-3">
                                <x-status-badge :status="$u->status" />
                            </td>
                            <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('users.edit', $u) }}" wire:navigate
                                       class="px-3 py-1 rounded-md bg-raised border border-line font-mono text-secondary text-[11px] font-semibold hover:bg-primary hover:text-white transition">
                                        Edit
                                    </a>
                                    @if ($u->id !== auth()->id())
                                        <button type="button"
                                                @click="openArchiveModal('{{ route('users.archive', $u) }}', '{{ addslashes($u->name) }}', '{{ $u->email }}')"
                                                class="px-3 py-1 rounded-full bg-error-soft text-[#B91C1C] text-[11px] font-semibold hover:bg-error hover:text-white transition">
                                            Archive
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted">
            <span>Menampilkan {{ format_number($users->firstItem() ?? 0) }}–{{ format_number($users->lastItem() ?? 0) }} dari {{ format_number($users->total()) }} pengguna</span>
            <div>{{ $users->links() }}</div>
        </div>
    </x-card>
</x-layouts.app>
