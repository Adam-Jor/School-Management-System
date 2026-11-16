<x-layouts.app title="Profil Saya" :breadcrumbs="[['label' => 'Profil & Pengaturan Akun']]">
    <x-page-header
        title="Profil & Informasi Akun"
        subtitle="Lihat data pribadi sesuai role login dan perbarui kredensial akun Anda." />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kartu Ringkasan Profil (design.md Bagian 5: detail/profile) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-surface rounded-[20px] shadow-card overflow-hidden">
                <div class="h-24 bg-primary-soft"></div>
                <div class="px-6 pb-6">
                    <div class="flex items-end justify-between -mt-10 mb-3">
                        <x-avatar :name="$user->name" :src="$user->img_url" size="lg" />
                        <x-status-badge :status="$user->status" />
                    </div>
                    <h2 class="text-lg font-bold text-ink">{{ $user->name }}</h2>
                    <p class="text-xs text-muted mt-0.5">{{ $user->email }} · <span class="uppercase font-semibold text-primary">{{ $user->role }}</span></p>

                    <div class="mt-4 pt-4 border-t border-line space-y-2.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-muted">Username</span>
                            <span class="font-mono text-ink">{{ $user->username }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">No. Telepon</span>
                            <span class="font-medium text-ink">{{ $user->phone ?? '–' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Login Terakhir</span>
                            <span class="font-mono text-muted">{{ format_date($user->last_login_time, true) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jika user adalah Student: Tampilkan Rapor & Absensi Pribadi (PRD Bagian 8) -->
            @if ($user->student)
                <x-card class="space-y-3">
                    <h3 class="text-sm font-semibold text-ink">Data Akademik Siswa Pribadi</h3>
                    <div class="text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-muted">Kode Siswa</span>
                            <span class="font-semibold text-primary">{{ $user->student->student_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Kelas Aktif</span>
                            <span class="font-semibold text-ink">{{ $user->student->schoolClass?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Total Hadir</span>
                            <span class="font-semibold text-[#15803D]">{{ format_number($user->student->attendances->count()) }} Hari</span>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Form Update Profil -->
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <h2 class="text-base font-semibold text-ink mb-4">Perbarui Data Profil</h2>
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-xs font-medium text-ink mb-1.5">Nama Lengkap <span class="text-error font-bold">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus required class="edudash-input" />
                        @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-medium text-ink mb-1.5">Alamat Email <span class="text-error font-bold">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="edudash-input" />
                        @error('email') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-medium text-ink mb-1.5">Nomor Telepon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" class="edudash-input" />
                        @error('phone') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium text-ink mb-1.5">Ganti Kata Sandi (Opsional)</label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diganti…" class="edudash-input" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink mb-1.5">Ganti Foto Profil (WebP)</label>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"
                           class="block w-full text-xs text-muted file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border file:border-primary file:bg-surface file:text-primary file:font-semibold hover:file:bg-primary-soft" />
                </div>

                <div class="pt-4 border-t border-line flex justify-end">
                    <button type="submit" class="edudash-btn-primary text-sm">Simpan Profil</button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
