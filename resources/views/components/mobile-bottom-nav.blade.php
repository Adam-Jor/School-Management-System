@php
    $user = auth()->user();
@endphp

@if ($user)
    <nav aria-label="Navigasi bawah mobile"
         class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-surface/95 backdrop-blur border-t border-line px-2 py-1.5 shadow-[0_-4px_16px_rgba(15,23,42,0.05)]">
        <div class="max-w-md mx-auto grid grid-cols-5 gap-1">
            @if ($user->isTeacher())
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('dashboard', 'teacher.dashboard') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-squares-2x2 class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Beranda</span>
                </a>

                <a href="{{ route('attendances.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('attendances.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-calendar-days class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Absensi</span>
                </a>

                <a href="{{ route('students.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('students.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-academic-cap class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Siswa</span>
                </a>

                <a href="{{ route('classes.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('classes.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-building-library class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Kelas</span>
                </a>

                <a href="{{ route('profile.show') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('profile.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-user-circle class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Profil</span>
                </a>
            @elseif ($user->isStudent())
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('dashboard', 'student.dashboard') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-squares-2x2 class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Beranda</span>
                </a>

                <a href="{{ route('grades.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('grades.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-document-text class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Nilai</span>
                </a>

                <a href="{{ route('attendances.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('attendances.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-calendar-days class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Absensi</span>
                </a>

                <a href="{{ route('students.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('students.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-academic-cap class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Data Diri</span>
                </a>

                <a href="{{ route('profile.show') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('profile.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-user-circle class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Profil</span>
                </a>
            @else
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('dashboard', 'admin.dashboard') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-squares-2x2 class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Beranda</span>
                </a>

                <a href="{{ route('students.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('students.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-academic-cap class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Siswa</span>
                </a>

                <a href="{{ route('teachers.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('teachers.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-user-circle class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Guru</span>
                </a>

                <a href="{{ route('attendances.index') }}" wire:navigate
                   class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium transition-colors duration-150 {{ request()->routeIs('attendances.*') ? 'text-primary bg-primary-soft/60' : 'text-muted hover:text-ink' }}">
                    <x-heroicon-o-calendar-days class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Absensi</span>
                </a>

                <button type="button"
                        @click="mobileSidebarOpen = true"
                        class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-medium text-muted hover:text-ink transition-colors duration-150">
                    <x-heroicon-o-bars-3 class="size-5 stroke-[1.75]" />
                    <span class="mt-0.5 truncate">Menu</span>
                </button>
            @endif
        </div>
    </nav>
@endif
