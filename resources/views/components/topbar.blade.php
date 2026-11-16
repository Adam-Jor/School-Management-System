@props([
    'breadcrumbs' => [],
])
@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-30 h-16 bg-surface/95 backdrop-blur-xs border-b border-line px-6 lg:px-8 flex items-center justify-between gap-4">
    <!-- Left: Hamburger + Folder Icon + Breadcrumb -->
    <div class="flex items-center gap-3 min-w-0">
        <button type="button"
                @click="mobileSidebarOpen = !mobileSidebarOpen"
                aria-label="Buka menu navigasi"
                class="lg:hidden size-9 rounded-full bg-sidebar border border-line flex items-center justify-center text-ink">
            <x-heroicon-o-bars-3 class="size-5" />
        </button>

        <div class="size-8 rounded-lg bg-primary-soft/80 text-primary flex items-center justify-center shrink-0">
            <x-heroicon-o-folder class="size-4" />
        </div>

        <nav aria-label="Breadcrumb" class="text-[13px] truncate">
            <ol class="flex items-center gap-2 text-muted font-medium">
                <li>
                    <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-primary transition">EDUDASH</a>
                </li>
                @foreach ($breadcrumbs as $crumb)
                    <li class="text-muted/60">›</li>
                    <li>
                        @if (!empty($crumb['url']) && !$loop->last)
                            <a href="{{ $crumb['url'] }}" wire:navigate class="hover:text-primary transition">{{ $crumb['label'] }}</a>
                        @else
                            <span class="text-ink font-semibold">{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    <!-- Right: Global Search + Tahun Ajaran Pill + Bell + User Avatar Dropdown -->
    <div class="flex items-center gap-3">
        <!-- Global Search Pill -->
        <form action="{{ route('dashboard') }}" method="GET" class="hidden md:flex items-center relative">
            <x-heroicon-o-magnifying-glass class="size-4 text-muted absolute left-3.5 pointer-events-none" />
            <input type="search"
                   name="global_q"
                   value="{{ request('global_q') }}"
                   placeholder="Cari siswa, guru, atau kelas..."
                   aria-label="Pencarian global"
                   class="w-64 lg:w-72 h-9 pl-9 pr-14 rounded-full bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition" />
            <span class="absolute right-2.5 px-2 py-0.5 rounded-full bg-surface border border-line text-[10px] font-mono text-muted">
                Ctrl+K
            </span>
        </form>

        <!-- Indikator Tahun Ajaran -->
        <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sidebar border border-line text-xs font-medium text-ink">
            <span class="size-2 rounded-full bg-success"></span>
            <span>Tahun Ajaran 2026/2027</span>
        </div>

        <!-- Bell Notification -->
        <a href="{{ route('dashboard') }}#aktivitas"
           aria-label="Notifikasi aktivitas terbaru"
           class="relative size-9 rounded-full bg-sidebar border border-line flex items-center justify-center text-ink hover:bg-primary-soft transition">
            <x-heroicon-o-bell class="size-4" />
            <span class="absolute top-1.5 right-2 size-2 rounded-full bg-primary"></span>
        </a>

        <!-- Avatar Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button type="button"
                    @click="open = !open"
                    @click.outside="open = false"
                    aria-label="Menu profil pengguna"
                    class="flex items-center gap-2.5 pl-1 pr-2.5 py-1 rounded-full hover:bg-sidebar transition">
                <x-avatar :name="$user?->name ?? 'User'" :src="$user?->img_url" size="sm" />
                <span class="hidden sm:inline text-[13px] font-semibold text-ink">{{ Str::words($user?->name ?? 'Admin', 1, '') }}</span>
                <x-heroicon-o-chevron-down class="size-3.5 text-muted" />
            </button>

            <div x-show="open"
                 x-transition.origin.top.right
                 style="display: none;"
                 class="absolute right-0 mt-2 w-56 bg-surface rounded-[20px] p-2 shadow-lg border border-line z-50">
                <div class="px-3 py-2 border-b border-line mb-1">
                    <p class="text-xs font-semibold text-ink truncate">{{ $user?->name }}</p>
                    <p class="text-[11px] text-muted truncate">{{ $user?->email }} · <span class="uppercase font-semibold text-primary">{{ $user?->role }}</span></p>
                </div>
                <a href="{{ route('profile.show') }}" wire:navigate
                   class="flex items-center gap-2.5 px-3 py-2 rounded-full text-xs font-medium text-ink hover:bg-primary-soft transition">
                    <x-heroicon-o-user class="size-4 text-primary" />
                    <span>Profil & Data Pribadi</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-full text-xs font-medium text-error hover:bg-error-soft transition">
                        <x-heroicon-o-arrow-right-on-rectangle class="size-4" />
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
