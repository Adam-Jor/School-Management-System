<x-layouts.app title="Living Styleguide & Design System" :breadcrumbs="[['label' => 'Design System']]">
    <x-page-header
        title="Living Styleguide & UI Primitives"
        subtitle="Dokumentasi token desain terpadu, komponen antarmuka, dan standar aksesibilitas EDUDASH" />

    <div class="space-y-8">
        <!-- 1. Color Tokens -->
        <x-card>
            <div class="flex items-center justify-between pb-4 border-b border-line mb-5">
                <div>
                    <h3 class="text-base font-semibold text-ink">1. Token Warna & Rasio Kontras (WCAG AA)</h3>
                    <p class="text-xs text-muted mt-0.5">Palet semantik adaptif (Light & Dark theme) berbasis CSS Custom Properties</p>
                </div>
                <button type="button" onclick="window.toggleTheme()" class="edudash-btn-secondary text-xs h-8 px-3 gap-1.5">
                    <x-heroicon-o-moon class="size-4" />
                    <span>Uji Dark Mode</span>
                </button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <div class="p-3 rounded-xl border border-line bg-page">
                    <div class="h-10 rounded-lg bg-page border border-line mb-2"></div>
                    <p class="text-xs font-semibold text-ink">Page Canvas</p>
                    <p class="font-mono text-[11px] text-muted">--color-bg-page</p>
                </div>
                <div class="p-3 rounded-xl border border-line bg-surface">
                    <div class="h-10 rounded-lg bg-surface border border-line mb-2"></div>
                    <p class="text-xs font-semibold text-ink">Surface Card</p>
                    <p class="font-mono text-[11px] text-muted">--color-bg-surface</p>
                </div>
                <div class="p-3 rounded-xl border border-line bg-sidebar">
                    <div class="h-10 rounded-lg bg-sidebar border border-line mb-2"></div>
                    <p class="text-xs font-semibold text-ink">Sidebar / Muted</p>
                    <p class="font-mono text-[11px] text-muted">--color-bg-sidebar</p>
                </div>
                <div class="p-3 rounded-xl border border-line bg-surface">
                    <div class="h-10 rounded-lg bg-primary mb-2 flex items-center justify-center text-white font-mono text-xs font-bold">4.5:1 AA</div>
                    <p class="text-xs font-semibold text-ink">Brand Primary</p>
                    <p class="font-mono text-[11px] text-muted">#4F46E5 / #6366F1</p>
                </div>
                <div class="p-3 rounded-xl border border-line bg-surface">
                    <div class="h-10 rounded-lg bg-emerald-600 mb-2 flex items-center justify-center text-white font-mono text-xs font-bold">4.7:1 AA</div>
                    <p class="text-xs font-semibold text-ink">Success</p>
                    <p class="font-mono text-[11px] text-muted">#16A34A</p>
                </div>
                <div class="p-3 rounded-xl border border-line bg-surface">
                    <div class="h-10 rounded-lg bg-rose-600 mb-2 flex items-center justify-center text-white font-mono text-xs font-bold">4.9:1 AA</div>
                    <p class="text-xs font-semibold text-ink">Danger / Error</p>
                    <p class="font-mono text-[11px] text-muted">#DC2626</p>
                </div>
            </div>
        </x-card>

        <!-- 2. Interactive Primitives & Feedback Trigger -->
        <x-card>
            <div class="pb-4 border-b border-line mb-5">
                <h3 class="text-base font-semibold text-ink">2. Interaksi UI Primitives (Command Palette, Drawer, Toast Multi-Varian)</h3>
                <p class="text-xs text-muted mt-0.5">Komponen interaktif modern untuk efisiensi pengguna dan umpan balik responsif</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Command Palette Trigger -->
                <div class="p-4 rounded-xl border border-line bg-sidebar flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="size-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                <x-heroicon-o-magnifying-glass class="size-4" />
                            </span>
                            <h4 class="text-xs font-semibold text-ink">Command Palette</h4>
                        </div>
                        <p class="text-xs text-muted">Quick jump navigasi & shortcut aksi cepat via keyboard shortcut.</p>
                    </div>
                    <button type="button"
                            @click="$dispatch('open-command-palette')"
                            class="edudash-btn-primary text-xs h-9 mt-4 w-full justify-between">
                        <span>Buka Command Palette</span>
                        <kbd class="edudash-keycap bg-white/20 text-white border-white/20">Ctrl+K</kbd>
                    </button>
                </div>

                <!-- Slide-Over Drawer Trigger -->
                <div class="p-4 rounded-xl border border-line bg-sidebar flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="size-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                <x-heroicon-o-bars-3-bottom-right class="size-4" />
                            </span>
                            <h4 class="text-xs font-semibold text-ink">Slide-Over / Bottom Sheet</h4>
                        </div>
                        <p class="text-xs text-muted">Inspeksi detail data mendalam tanpa context loss atau reload halaman.</p>
                    </div>
                    <button type="button"
                            onclick="window.openDrawer({
                                title: 'Profil Siswa Contoh',
                                subtitle: 'STD-001 · NISN 008123984',
                                badge: 'Aktif',
                                meta: [
                                    { label: 'Kelas Rombel', value: 'X-IPA-1' },
                                    { label: 'Wali Kelas', value: 'Budi Santoso, S.Pd' },
                                    { label: 'Tahun Masuk', value: '2024' },
                                    { label: 'Status Absensi', value: '98% Hadir' }
                                ],
                                contentHtml: '<p class=\'text-xs text-secondary\'>Siswa terdaftar aktif pada semester ganjil tahun ajaran 2024/2025.</p>',
                                actions: [{ label: 'Tutup Panel', url: 'javascript:void(0)', class: 'edudash-btn-secondary text-xs h-8 px-3' }]
                            })"
                            class="edudash-btn-secondary text-xs h-9 mt-4 w-full">
                        Uji Slide-Over Drawer
                    </button>
                </div>

                <!-- Toast Notification Triggers -->
                <div class="p-4 rounded-xl border border-line bg-sidebar flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="size-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                <x-heroicon-o-bell-alert class="size-4" />
                            </span>
                            <h4 class="text-xs font-semibold text-ink">Sistem Toast & Undo</h4>
                        </div>
                        <p class="text-xs text-muted">4 status semantik dengan dukungan aksi pembatalan (Undo Action).</p>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 mt-4">
                        <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Data siswa berhasil disimpan', type: 'success' } }))"
                                class="px-2 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-medium hover:bg-emerald-100">
                            Success
                        </button>
                        <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Data rombel diarsipkan', type: 'warning', undoUrl: '#undo' } }))"
                                class="px-2 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-[11px] font-medium hover:bg-amber-100">
                            Warning + Undo
                        </button>
                        <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Format kode siswa tidak valid', type: 'error' } }))"
                                class="px-2 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-[11px] font-medium hover:bg-rose-100">
                            Error
                        </button>
                        <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Tahun ajaran berhasil diubah', type: 'info' } }))"
                                class="px-2 py-1.5 rounded-lg bg-sky-50 text-sky-700 text-[11px] font-medium hover:bg-sky-100">
                            Info
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- 3. Form Field Primitives -->
        <x-card>
            <div class="pb-4 border-b border-line mb-5">
                <h3 class="text-base font-semibold text-ink">3. Form Field Components Terpadu</h3>
                <p class="text-xs text-muted mt-0.5">Wrapper komponen Blade terpadu (&lt;x-form-input&gt;, &lt;x-form-select&gt;, &lt;x-form-textarea&gt;)</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <x-form-input
                    name="example_student_name"
                    label="Nama Lengkap Siswa"
                    placeholder="Contoh: Muhammad Farhan"
                    required="true"
                    help="Gunakan nama lengkap sesuai akta kelahiran" />

                <x-form-select
                    name="example_class"
                    label="Kelas Penempatan"
                    required="true"
                    placeholder="-- Pilih Kelas --">
                    <option value="1">X-IPA-1 (Reguler)</option>
                    <option value="2">X-IPA-2 (Reguler)</option>
                    <option value="3">XI-IPS-1 (Peminatan)</option>
                </x-form-select>

                <x-form-textarea
                    name="example_notes"
                    label="Catatan Khusus Siswa"
                    placeholder="Catatan prestasi atau riwayat kesehatan"
                    rows="2" />
            </div>
        </x-card>

        <!-- 4. Buttons, Badges & Mobile Touch Targets -->
        <x-card>
            <div class="pb-4 border-b border-line mb-5">
                <h3 class="text-base font-semibold text-ink">4. Buttons, Badges & Mobile Touch Targets</h3>
                <p class="text-xs text-muted mt-0.5">Standar tombol dan status badge dengan target sentuh memadai (>= 44x44px di mobile)</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="edudash-btn-primary">Tombol Utama (Primary)</button>
                <button type="button" class="edudash-btn-secondary">Tombol Sekunder</button>
                <button type="button" class="h-10 px-4 rounded-lg bg-rose-600 text-white font-medium text-sm hover:bg-rose-700">Tombol Bahaya (Danger)</button>
                <button type="button" disabled class="edudash-btn-secondary opacity-50 cursor-not-allowed">Disabled</button>

                <div class="h-6 w-px bg-line mx-2"></div>

                <x-status-badge status="Aktif" />
                <x-status-badge status="Pindah" />
                <x-status-badge status="Menunggu" />
            </div>
        </x-card>
    </div>
</x-layouts.app>
