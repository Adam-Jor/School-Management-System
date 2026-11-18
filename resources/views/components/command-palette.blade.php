@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $isTeacher = $user?->isTeacher() ?? false;
@endphp

<div x-data="{
        open: false,
        search: '',
        activeIndex: 0,
        items: [
            // Navigasi
            { id: 'nav-dash', title: 'Dashboard', category: 'Navigasi', url: '{{ route('dashboard') }}' },
            { id: 'nav-students', title: 'Data Siswa', category: 'Navigasi', url: '{{ route('students.index') }}' },
            @if ($isAdmin)
                { id: 'nav-teachers', title: 'Data Guru', category: 'Navigasi', url: '{{ route('teachers.index') }}' },
            @endif
            { id: 'nav-classes', title: 'Data Kelas', category: 'Navigasi', url: '{{ route('classes.index') }}' },
            @if ($isAdmin || $isTeacher)
                { id: 'nav-subjects', title: 'Mata Pelajaran', category: 'Navigasi', url: '{{ route('subjects.index') }}' },
            @endif
            { id: 'nav-attendances', title: 'Absensi Siswa', category: 'Navigasi', url: '{{ route('attendances.index') }}' },
            { id: 'nav-grades', title: 'Nilai & Rapor', category: 'Navigasi', url: '{{ route('grades.index') }}' },
            { id: 'nav-schedules', title: 'Jadwal Pelajaran', category: 'Navigasi', url: '{{ route('schedules.index') }}' },
            @if ($isAdmin)
                { id: 'nav-users', title: 'Manajemen Pengguna', category: 'Navigasi', url: '{{ route('users.index') }}' },
                { id: 'nav-archive', title: 'Arsip & Pemulihan Data', category: 'Navigasi', url: '{{ route('archive.index') }}' },
            @endif
            { id: 'nav-profile', title: 'Pengaturan Profil Saya', category: 'Navigasi', url: '{{ route('profile.show') }}' },
            { id: 'nav-ds', title: 'Living Styleguide & Design System', category: 'Navigasi', url: '{{ route('design-system.index') }}' },

            // Aksi Cepat
            @if ($isAdmin)
                { id: 'act-add-student', title: 'Tambah Siswa Baru', category: 'Aksi Cepat', url: '{{ route('students.create') }}' },
                { id: 'act-add-teacher', title: 'Tambah Guru Baru', category: 'Aksi Cepat', url: '{{ route('teachers.create') }}' },
                { id: 'act-add-class', title: 'Tambah Kelas Baru', category: 'Aksi Cepat', url: '{{ route('classes.create') }}' },
            @endif
            @if ($isAdmin || $isTeacher)
                { id: 'act-attendance-today', title: 'Catat Absensi Hari Ini', category: 'Aksi Cepat', url: '{{ route('attendances.index') }}' },
                { id: 'act-grade-input', title: 'Input Nilai Siswa', category: 'Aksi Cepat', url: '{{ route('grades.index') }}' },
            @endif
            { id: 'act-toggle-theme', title: 'Beralih Mode Gelap / Terang', category: 'Preferensi', action: 'toggleTheme' }
        ],
        get filteredItems() {
            const q = this.search.toLowerCase().trim();
            if (!q) return this.items;
            return this.items.filter(item => {
                return item.title.toLowerCase().includes(q) || item.category.toLowerCase().includes(q);
            });
        },
        openPalette() {
            this.open = true;
            this.search = '';
            this.activeIndex = 0;
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        },
        closePalette() {
            this.open = false;
        },
        selectItem(item) {
            if (!item) return;
            this.closePalette();
            if (item.action === 'toggleTheme') {
                window.toggleTheme?.();
            } else if (item.url) {
                window.location.href = item.url;
            }
        },
        selectActive() {
            const items = this.filteredItems;
            if (items.length > 0 && items[this.activeIndex]) {
                this.selectItem(items[this.activeIndex]);
            }
        },
        prevItem() {
            if (this.activeIndex > 0) {
                this.activeIndex--;
            } else {
                this.activeIndex = Math.max(0, this.filteredItems.length - 1);
            }
        },
        nextItem() {
            if (this.activeIndex < this.filteredItems.length - 1) {
                this.activeIndex++;
            } else {
                this.activeIndex = 0;
            }
        }
     }"
     @keydown.window.prevent.cmd.k="openPalette()"
     @keydown.window.prevent.ctrl.k="openPalette()"
     @open-command-palette.window="openPalette()"
     @keydown.escape.window="closePalette()"
     class="relative z-50">

    <!-- Modal Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closePalette()"
         class="fixed inset-0 bg-ink/40 backdrop-blur-sm"
         style="display: none;"></div>

    <!-- Modal Dialog -->
    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 sm:pt-24 pointer-events-none"
         style="display: none;">

        <div class="w-full max-w-xl bg-surface border border-line rounded-2xl shadow-2xl pointer-events-auto overflow-hidden flex flex-col max-h-[80vh]"
             @click.away="closePalette()">

            <!-- Search Header -->
            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-line">
                <x-heroicon-o-magnifying-glass class="size-5 text-muted shrink-0" />
                <input type="text"
                       x-ref="searchInput"
                       x-model="search"
                       @keydown.arrow-down.prevent="nextItem()"
                       @keydown.arrow-up.prevent="prevItem()"
                       @keydown.enter.prevent="selectActive()"
                       placeholder="Ketik pencarian atau navigasi... (mis. Siswa, Absensi, Tambah)"
                       class="flex-1 bg-transparent border-0 text-sm text-ink placeholder:text-muted focus:outline-none focus:ring-0" />
                <button type="button"
                        @click="closePalette()"
                        class="text-xs text-muted font-mono px-1.5 py-0.5 rounded bg-raised border border-line hover:text-ink">
                    ESC
                </button>
            </div>

            <!-- Items List -->
            <div class="overflow-y-auto p-2 divide-y divide-line/40 max-h-[380px]">
                <template x-if="filteredItems.length === 0">
                    <div class="p-8 text-center text-sm text-muted">
                        <x-heroicon-o-face-frown class="size-8 mx-auto mb-2 text-muted/60" />
                        <p class="font-medium text-ink">Tidak ada hasil ditemukan</p>
                        <p class="text-xs text-secondary mt-1">Coba kata kunci lain seperti "Siswa", "Guru", atau "Jadwal"</p>
                    </div>
                </template>

                <template x-for="(item, index) in filteredItems" :key="item.id">
                    <div @click="selectItem(item)"
                         @mouseenter="activeIndex = index"
                         :class="{
                             'bg-primary-soft text-primary font-medium': activeIndex === index,
                             'text-ink hover:bg-sidebar': activeIndex !== index
                         }"
                         class="flex items-center justify-between px-3.5 py-2.5 rounded-xl cursor-pointer text-xs transition-colors duration-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="size-7 rounded-lg bg-raised flex items-center justify-center shrink-0 text-secondary">
                                <x-heroicon-o-arrow-right class="size-3.5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm truncate" x-text="item.title"></p>
                                <p class="text-[11px] text-muted truncate" x-text="item.category"></p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-muted tabular-nums shrink-0" x-text="activeIndex === index ? '↵ Buka' : ''"></span>
                    </div>
                </template>
            </div>

            <!-- Footer Shortcuts -->
            <div class="px-4 py-2.5 bg-sidebar border-t border-line flex items-center justify-between text-[11px] text-muted font-mono">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1 py-0.5 rounded bg-surface border border-line">↑</kbd> <kbd class="px-1 py-0.5 rounded bg-surface border border-line">↓</kbd> Navigasi</span>
                    <span><kbd class="px-1 py-0.5 rounded bg-surface border border-line">↵</kbd> Pilih</span>
                </div>
                <span>EDUDASH Quick Jump</span>
            </div>
        </div>
    </div>
</div>
