<x-card>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-[220px] max-w-xs">
                <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-3 pointer-events-none" />
                <input type="search"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama, username, atau email"
                       aria-label="Cari pengguna"
                       class="w-full h-10 pl-9 pr-3.5 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
            </div>

            <select wire:model.live="role"
                    aria-label="Filter role pengguna"
                    class="h-10 px-3.5 rounded-lg bg-sidebar border border-line text-xs font-normal text-ink focus:border-primary focus:outline-none">
                <option value="">Semua role</option>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
        </div>

        <span class="font-mono text-xs text-muted tabular-nums">
            {{ format_number($users->total()) }} pengguna
        </span>
    </div>

    <div wire:loading.flex class="hidden items-center gap-3 mb-4 px-3 py-2 rounded-lg bg-sidebar border border-line text-xs text-muted">
        <span class="loading loading-spinner loading-xs text-primary"></span>
        <span>Memuat data pengguna...</span>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-60">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-line text-xs font-normal text-muted">
                    <th class="py-3 pl-2 text-right w-10">No</th>
                    <th class="py-3 px-3">
                        <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-ink">
                            <span>Pengguna</span>
                            @if ($sortField === 'name') <span class="font-mono text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </button>
                    </th>
                    <th class="py-3 px-3">Username</th>
                    <th class="py-3 px-3">Role</th>
                    <th class="py-3 px-3">Login Terakhir</th>
                    <th class="py-3 px-3">Status</th>
                    <th class="py-3 pr-2 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-sm">
                @forelse ($users as $idx => $u)
                    <tr wire:key="user-row-{{ $u->id }}" class="group hover:bg-sidebar transition-colors duration-150">
                        <td class="py-3 pl-2 text-right font-mono text-xs text-muted tabular-nums">
                            {{ format_number(($users->currentPage() - 1) * $users->perPage() + $idx + 1) }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <x-avatar :name="$u->name" :src="$u->img_url" />
                                <div>
                                    <p class="text-[13px] font-medium text-ink">{{ $u->name }}</p>
                                    <p class="text-xs text-muted">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="edudash-keycap">{{ $u->username }}</span>
                        </td>
                        <td class="py-3 px-3 text-xs font-medium text-secondary">
                            {{ ucfirst($u->role) }}
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-muted whitespace-nowrap tabular-nums">
                            {{ format_date($u->last_login_time, true) }}
                        </td>
                        <td class="py-3 px-3">
                            <x-status-badge :status="$u->status" />
                        </td>
                        <td class="py-3 pr-2 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('users.edit', $u) }}" wire:navigate
                                   class="h-7 px-2.5 inline-flex items-center rounded-md bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150">
                                    Edit
                                </a>
                                @if ($u->id !== auth()->id())
                                    <button type="button"
                                            @click="openArchiveModal('{{ route('users.archive', $u) }}', '{{ addslashes($u->name) }}', '{{ $u->email }}')"
                                            class="h-7 px-2.5 inline-flex items-center rounded-md bg-error-soft text-[#B91C1C] text-xs font-medium hover:bg-error hover:text-white transition-colors duration-150">
                                        Arsip
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state title="Pengguna tidak ditemukan" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted tabular-nums">
        <span>Menampilkan {{ format_number($users->firstItem() ?? 0) }}–{{ format_number($users->lastItem() ?? 0) }} dari {{ format_number($users->total()) }} pengguna</span>
        <div>{{ $users->links() }}</div>
    </div>
</x-card>
