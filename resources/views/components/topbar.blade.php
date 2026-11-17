@props([
    'breadcrumbs' => [],
])
@php
    $user = auth()->user();
@endphp

<header x-data="{ mobileSearchOpen: {{ request()->filled('global_q') ? 'true' : 'false' }} }"
        class="sticky top-0 z-30 bg-surface/95 backdrop-blur border-b border-line">
    <div class="h-14 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-2 sm:gap-4">
        <!-- Left: Hamburger + Breadcrumb -->
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
            <button type="button"
                    @click="mobileSidebarOpen = !mobileSidebarOpen"
                    aria-label="Buka menu navigasi"
                    class="lg:hidden size-9 rounded-lg bg-surface border border-line flex items-center justify-center text-ink shrink-0 active:scale-95 transition">
                <x-heroicon-o-bars-3 class="size-[18px] stroke-[1.5]" />
            </button>

            <nav aria-label="Breadcrumb" class="text-xs sm:text-[13px] truncate">
                <ol class="flex items-center gap-1.5 sm:gap-2 text-muted font-normal truncate">
                    <li class="hidden xs:inline sm:inline">
                        <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-ink transition-colors duration-150">Edudash</a>
                    </li>
                    @foreach ($breadcrumbs as $crumb)
                        <li class="hidden xs:inline sm:inline text-muted/60">/</li>
                        <li class="truncate">
                            @if (!empty($crumb['url']) && !$loop->last)
                                <a href="{{ $crumb['url'] }}" wire:navigate class="hover:text-ink transition-colors duration-150">{{ $crumb['label'] }}</a>
                            @else
                                <span class="text-ink font-medium truncate">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <!-- Right: Global Search + Tahun Ajaran + Bell + User Avatar Dropdown -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Mobile Search Toggle Button -->
            <button type="button"
                    @click="mobileSearchOpen = !mobileSearchOpen"
                    aria-label="Buka pencarian"
                    class="md:hidden size-9 rounded-lg bg-sidebar border border-line flex items-center justify-center text-secondary hover:text-ink">
                <x-heroicon-o-magnifying-glass class="size-[18px] stroke-[1.5]" />
            </button>

            <!-- Desktop Global Search with Keycap -->
            <form action="{{ route('dashboard') }}" method="GET" class="hidden md:flex items-center relative">
                <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 pointer-events-none" />
                <input type="search"
                       name="global_q"
                       value="{{ request('global_q') }}"
                       placeholder="Cari nama atau kode"
                       aria-label="Pencarian global"
                       class="w-60 lg:w-64 h-9 pl-9 pr-14 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15 transition-colors duration-150" />
                <kbd class="edudash-keycap absolute right-2">Ctrl+K</kbd>
            </form>

            <!-- Indikator Tahun Ajaran -->
            <div class="hidden sm:flex items-center gap-2 h-9 px-3 rounded-lg bg-sidebar border border-line text-xs font-normal text-secondary">
                <span class="size-2 rounded-full bg-success"></span>
                <span class="tabular-nums">TA 2026/2027</span>
            </div>

            <!-- Bell Notification -->
            <a href="{{ route('dashboard') }}#aktivitas"
               aria-label="Log aktivitas"
               class="relative size-9 rounded-lg bg-sidebar border border-line flex items-center justify-center text-secondary hover:bg-raised hover:text-ink transition-colors duration-150">
                <x-heroicon-o-bell class="size-[18px] stroke-[1.5]" />
            </a>

            <!-- Avatar Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        aria-label="Menu profil pengguna"
                        class="h-9 flex items-center gap-2 pl-1.5 pr-2 sm:pr-2.5 rounded-lg hover:bg-raised transition-colors duration-150">
                    <x-avatar :name="$user?->name ?? 'User'" :src="$user?->img_url" size="sm" />
                    <span class="hidden sm:inline text-[13px] font-medium text-ink">{{ Str::words($user?->name ?? 'Admin', 1, '') }}</span>
                    <x-heroicon-o-chevron-down class="size-3.5 stroke-[1.5] text-muted" />
                </button>

                <div x-show="open"
                     x-transition.opacity.duration.150ms
                     style="display: none;"
                     class="absolute right-0 mt-2 w-56 bg-surface rounded-xl p-1.5 shadow-lg border border-line z-50">
                    <div class="px-3 py-2 border-b border-line mb-1">
                        <p class="text-xs font-medium text-ink truncate">{{ $user?->name }}</p>
                        <p class="text-[11px] text-muted truncate">{{ $user?->email }} · <span class="font-medium text-secondary">{{ ucfirst($user?->role ?? '') }}</span></p>
                    </div>
                    <a href="{{ route('profile.show') }}" wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-normal text-ink hover:bg-raised transition-colors duration-150">
                        <x-heroicon-o-user class="size-4 stroke-[1.5] text-muted" />
                        <span>Profil & Data Pribadi</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-0.5">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-normal text-error hover:bg-error-soft transition-colors duration-150">
                            <x-heroicon-o-arrow-right-on-rectangle class="size-4 stroke-[1.5]" />
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsible Mobile Search Bar -->
    <div x-show="mobileSearchOpen"
         x-transition.opacity.duration.150ms
         style="display: none;"
         class="md:hidden px-4 pb-3 border-t border-line/60 bg-surface">
        <form action="{{ route('dashboard') }}" method="GET" class="pt-2.5 flex items-center gap-2">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="size-4 stroke-[1.5] text-muted absolute left-3 top-2.5 pointer-events-none" />
                <input type="search"
                       name="global_q"
                       value="{{ request('global_q') }}"
                       placeholder="Cari siswa atau kelas…"
                       aria-label="Pencarian mobile"
                       class="w-full h-9 pl-9 pr-3 rounded-lg bg-sidebar border border-line text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
            </div>
            <button type="submit" class="edudash-btn-primary h-9 px-3.5 text-xs">Cari</button>
        </form>
    </div>
</header>
