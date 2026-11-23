@php
    $user = auth()->user();
    $counts = edudash_dashboard_stats();
@endphp

<aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-[240px] bg-sidebar border-r border-line flex flex-col justify-between transition-transform duration-200 ease-unslop">

    <div class="p-4 overflow-y-auto">
        <!-- 1. Logo School Management -->
        <div class="flex items-center justify-between px-2 h-9">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center">
                <img src="{{ asset('img/logo-school-management.png') }}?v={{ filemtime(public_path('img/logo-school-management.png')) }}"
                     alt="School Management"
                     class="h-8 w-auto max-w-[150px] object-contain" />
            </a>
            <div class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-muted tabular-nums">2026/27</span>
                <button type="button" @click="mobileSidebarOpen = false" aria-label="Tutup menu navigasi"
                    class="lg:hidden size-7 rounded-lg border border-line bg-surface flex items-center justify-center text-muted hover:text-ink">
                    <x-heroicon-o-x-mark class="size-4 stroke-[1.5]" />
                </button>
            </div>
        </div>

        <!-- 2. Kartu Sekolah / Tahun Ajaran -->
        <div class="mt-4 bg-surface border border-line rounded-xl p-3">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-success shrink-0"></span>
                <p class="text-[13px] font-medium text-ink truncate">SMA Contoh Nusantara</p>
            </div>
            <p class="text-xs text-muted mt-1 pl-4 truncate">
                {{ ucfirst($user?->role ?? 'User') }} · Semester Ganjil
            </p>
        </div>

        <!-- 3. Menu utama (24px above, 8px below, 36px rows, 4px gap) -->
        <div class="mt-6">
            <p class="px-2.5 mb-2 text-xs font-normal text-muted">Menu utama</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] flex items-center justify-center shrink-0">
                                <x-heroicon-o-squares-2x2
                                    class="size-[18px] stroke-[1.5] {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted' }}" />
                            </span>
                            <span>Dashboard</span>
                        </span>
                    </a>
                </li>

                @if ($user?->isAdmin())
                    <li>
                        <a href="{{ route('users.index') }}" wire:navigate
                            class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('users.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                            <span class="flex items-center gap-2.5">
                                <span class="w-[18px] flex items-center justify-center shrink-0">
                                    <x-heroicon-o-users
                                        class="size-[18px] stroke-[1.5] {{ request()->routeIs('users.*') ? 'text-primary' : 'text-muted' }}" />
                                </span>
                                <span>Data Pengguna</span>
                            </span>
                            <span
                                class="font-mono text-xs text-muted tabular-nums">{{ format_number($counts['users'] ?? 0) }}</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('students.index') }}" wire:navigate
                        class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('students.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] flex items-center justify-center shrink-0">
                                <x-heroicon-o-academic-cap
                                    class="size-[18px] stroke-[1.5] {{ request()->routeIs('students.*') ? 'text-primary' : 'text-muted' }}" />
                            </span>
                            <span>{{ $user?->isStudent() ? 'Data Pribadi Siswa' : 'Data Siswa' }}</span>
                        </span>
                        @unless ($user?->isStudent())
                            <span
                                class="font-mono text-xs text-muted tabular-nums">{{ format_number($counts['students'] ?? 0) }}</span>
                        @endunless
                    </a>
                </li>

                @if ($user?->isAdmin())
                    <li>
                        <a href="{{ route('teachers.index') }}" wire:navigate
                            class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('teachers.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                            <span class="flex items-center gap-2.5">
                                <span class="w-[18px] flex items-center justify-center shrink-0">
                                    <x-heroicon-o-user-circle
                                        class="size-[18px] stroke-[1.5] {{ request()->routeIs('teachers.*') ? 'text-primary' : 'text-muted' }}" />
                                </span>
                                <span>Data Guru</span>
                            </span>
                            <span
                                class="font-mono text-xs text-muted tabular-nums">{{ format_number($counts['teachers'] ?? 0) }}</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('classes.index') }}" wire:navigate
                        class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('classes.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] flex items-center justify-center shrink-0">
                                <x-heroicon-o-building-library
                                    class="size-[18px] stroke-[1.5] {{ request()->routeIs('classes.*') ? 'text-primary' : 'text-muted' }}" />
                            </span>
                            <span>Data Kelas</span>
                        </span>
                        <span
                            class="font-mono text-xs text-muted tabular-nums">{{ format_number($counts['classes'] ?? 0) }}</span>
                    </a>
                </li>

                @if ($user?->isAdmin() || $user?->isTeacher())
                    <li>
                        <a href="{{ route('subjects.index') }}" wire:navigate
                            class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('subjects.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                            <span class="flex items-center gap-2.5">
                                <span class="w-[18px] flex items-center justify-center shrink-0">
                                    <x-heroicon-o-book-open
                                        class="size-[18px] stroke-[1.5] {{ request()->routeIs('subjects.*') ? 'text-primary' : 'text-muted' }}" />
                                </span>
                                <span>Mata Pelajaran</span>
                            </span>
                            <span
                                class="font-mono text-xs text-muted tabular-nums">{{ format_number($counts['subjects'] ?? 0) }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Akademik & laporan (24px above, 8px below) -->
        <div class="mt-6">
            <p class="px-2.5 mb-2 text-xs font-normal text-muted">Akademik & laporan</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('attendances.index') }}" wire:navigate
                        class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('attendances.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] flex items-center justify-center shrink-0">
                                <x-heroicon-o-calendar-days
                                    class="size-[18px] stroke-[1.5] {{ request()->routeIs('attendances.*') ? 'text-primary' : 'text-muted' }}" />
                            </span>
                            <span>Absensi Siswa</span>
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('grades.index') }}" wire:navigate
                        class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('grades.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] flex items-center justify-center shrink-0">
                                <x-heroicon-o-document-text
                                    class="size-[18px] stroke-[1.5] {{ request()->routeIs('grades.*') ? 'text-primary' : 'text-muted' }}" />
                            </span>
                            <span>Nilai & Rapor</span>
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('schedules.index') }}" wire:navigate
                        class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('schedules.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] flex items-center justify-center shrink-0">
                                <x-heroicon-o-clock
                                    class="size-[18px] stroke-[1.5] {{ request()->routeIs('schedules.*') ? 'text-primary' : 'text-muted' }}" />
                            </span>
                            <span>Jadwal Pelajaran</span>
                        </span>
                    </a>
                </li>


                @if ($user?->isAdmin())
                    <li>
                        <a href="{{ route('archive.index') }}" wire:navigate
                            class="h-9 flex items-center justify-between px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('archive.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
                            <span class="flex items-center gap-2.5">
                                <span class="w-[18px] flex items-center justify-center shrink-0">
                                    <x-heroicon-o-archive-box
                                        class="size-[18px] stroke-[1.5] {{ request()->routeIs('archive.*') ? 'text-primary' : 'text-muted' }}" />
                                </span>
                                <span>Arsip Data</span>
                            </span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- 4. Bagian Bawah (Sticky) -->
    <div class="p-4 border-t border-line">
        <a href="{{ route('profile.show') }}" wire:navigate
            class="h-9 flex items-center gap-2.5 px-2.5 rounded-lg text-sm transition-colors duration-150 {{ request()->routeIs('profile.*') ? 'bg-raised text-ink font-medium border border-line/80' : 'text-secondary font-normal hover:bg-raised/60 hover:text-ink' }}">
            <span class="w-[18px] flex items-center justify-center shrink-0">
                <x-heroicon-o-cog-6-tooth class="size-[18px] stroke-[1.5] text-muted" />
            </span>
            <span>Pengaturan Profil</span>
        </a>
    </div>
</aside>