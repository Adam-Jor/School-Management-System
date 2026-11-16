@php
    $user = auth()->user();
    $counts = \Illuminate\Support\Facades\Cache::remember('edudash_dashboard_stats', 180, function () {
        return [
            'users' => \App\Models\User::count(),
            'students' => \App\Models\Student::count(),
            'teachers' => \App\Models\Teacher::count(),
            'classes' => \App\Models\SchoolClass::count(),
            'subjects' => \App\Models\Subject::count(),
        ];
    });
@endphp

<aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-50 w-[240px] bg-sidebar border-r border-line flex flex-col justify-between transition-transform duration-200 ease-in-out">

    <div class="p-4 space-y-5 overflow-y-auto">
        <!-- 1. Logo EDUDASH -->
        <div class="flex items-center justify-between px-1 pt-1">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 group">
                <span class="size-9 rounded-full bg-ink text-white font-bold text-sm flex items-center justify-center tracking-tight">
                    ED
                </span>
                <span class="text-[19px] font-bold tracking-tight text-ink">EDUDASH</span>
            </a>
            <span class="px-2.5 py-0.5 rounded-full bg-primary-soft text-primary text-[10px] font-semibold">v1.0</span>
        </div>

        <!-- 2. Kartu Sekolah / Tahun Ajaran -->
        <div class="bg-surface border border-line rounded-2xl p-3.5 shadow-card">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-success shrink-0"></span>
                <p class="text-[13px] font-semibold text-ink truncate">SMA Contoh Nusantara</p>
            </div>
            <p class="text-[11px] text-muted mt-1 pl-4 truncate">
                {{ ucfirst($user?->role ?? 'User') }} · TA 2026/2027
            </p>
        </div>

        <!-- 3. Menu Navigasi -->
        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-muted">Menu Utama</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                        <span class="flex items-center gap-3">
                            <x-heroicon-o-squares-2x2 class="size-5 {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted' }}" />
                            <span>Dashboard</span>
                        </span>
                        @if (request()->routeIs('dashboard'))
                            <span class="size-1.5 rounded-full bg-primary"></span>
                        @endif
                    </a>
                </li>

                @if ($user?->isAdmin())
                    <li>
                        <a href="{{ route('users.index') }}" wire:navigate
                           class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('users.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                            <span class="flex items-center gap-3">
                                <x-heroicon-o-users class="size-5 {{ request()->routeIs('users.*') ? 'text-primary' : 'text-muted' }}" />
                                <span>Data Pengguna</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] {{ request()->routeIs('users.*') ? 'bg-primary text-white font-semibold' : 'bg-surface border border-line text-muted' }}">
                                {{ format_number($counts['users'] ?? 0) }}
                            </span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('students.index') }}" wire:navigate
                       class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('students.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                        <span class="flex items-center gap-3">
                            <x-heroicon-o-academic-cap class="size-5 {{ request()->routeIs('students.*') ? 'text-primary' : 'text-muted' }}" />
                            <span>Data Siswa</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ request()->routeIs('students.*') ? 'bg-primary text-white font-semibold' : 'bg-surface border border-line text-muted' }}">
                            {{ format_number($counts['students'] ?? 0) }}
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('teachers.index') }}" wire:navigate
                       class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('teachers.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                        <span class="flex items-center gap-3">
                            <x-heroicon-o-user-circle class="size-5 {{ request()->routeIs('teachers.*') ? 'text-primary' : 'text-muted' }}" />
                            <span>Data Guru</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ request()->routeIs('teachers.*') ? 'bg-primary text-white font-semibold' : 'bg-surface border border-line text-muted' }}">
                            {{ format_number($counts['teachers'] ?? 0) }}
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('classes.index') }}" wire:navigate
                       class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('classes.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                        <span class="flex items-center gap-3">
                            <x-heroicon-o-building-library class="size-5 {{ request()->routeIs('classes.*') ? 'text-primary' : 'text-muted' }}" />
                            <span>Data Kelas</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ request()->routeIs('classes.*') ? 'bg-primary text-white font-semibold' : 'bg-surface border border-line text-muted' }}">
                            {{ format_number($counts['classes'] ?? 0) }}
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('subjects.index') }}" wire:navigate
                       class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('subjects.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                        <span class="flex items-center gap-3">
                            <x-heroicon-o-book-open class="size-5 {{ request()->routeIs('subjects.*') ? 'text-primary' : 'text-muted' }}" />
                            <span>Mata Pelajaran</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ request()->routeIs('subjects.*') ? 'bg-primary text-white font-semibold' : 'bg-surface border border-line text-muted' }}">
                            {{ format_number($counts['subjects'] ?? 0) }}
                        </span>
                    </a>
                </li>

                @if ($user?->isAdmin())
                    <li>
                        <a href="{{ route('archive.index') }}" wire:navigate
                           class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm transition duration-150 {{ request()->routeIs('archive.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
                            <span class="flex items-center gap-3">
                                <x-heroicon-o-archive-box class="size-5 {{ request()->routeIs('archive.*') ? 'text-primary' : 'text-muted' }}" />
                                <span>Archive / Trash</span>
                            </span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- 4. Bagian Bawah (Sticky) -->
    <div class="p-4 space-y-3 border-t border-line/60">
        <div class="bg-primary-soft rounded-2xl p-3.5">
            <p class="text-xs font-semibold text-ink">Tips Navigasi Cepat</p>
            <p class="text-[11px] text-muted leading-relaxed mt-1">
                Gunakan pencarian global di bagian atas untuk mencari siswa, guru, atau kelas.
            </p>
        </div>

        <a href="{{ route('profile.show') }}" wire:navigate
           class="flex items-center gap-3 px-4 py-2 rounded-full text-sm transition duration-150 {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-ink font-semibold' : 'text-muted font-medium hover:bg-primary-soft/60 hover:text-ink' }}">
            <x-heroicon-o-cog-6-tooth class="size-5" />
            <span>Pengaturan Profil</span>
        </a>
    </div>
</aside>
