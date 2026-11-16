<x-layouts.app title="Tambah Pengguna" :breadcrumbs="[['label' => 'Data Pengguna', 'url' => route('users.index')], ['label' => 'Tambah Pengguna']]">
    <x-page-header title="Tambah Data Pengguna" subtitle="Buat akun baru pada tbl_users dengan role admin, teacher, atau student." />

    <x-card class="max-w-[720px]">
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-medium text-ink mb-1.5">Nama Lengkap <span class="text-error font-bold">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap…" autofocus required class="edudash-input" />
                    @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="username" class="block text-xs font-medium text-ink mb-1.5">Username <span class="text-error font-bold">*</span></label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Contoh: budi.santoso" required class="edudash-input" />
                    @error('username') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-ink mb-1.5">Alamat Email <span class="text-error font-bold">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: nama@sch.id" required class="edudash-input" />
                    @error('email') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-ink mb-1.5">Kata Sandi <span class="text-error font-bold">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter…" required class="edudash-input" />
                    @error('password') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role" class="block text-xs font-medium text-ink mb-1.5">Role Hak Akses <span class="text-error font-bold">*</span></label>
                    <select id="role" name="role" required class="edudash-select">
                        <option value="student" @selected(old('role') === 'student')>Student (Read-Only Data Pribadi)</option>
                        <option value="teacher" @selected(old('role') === 'teacher')>Teacher (Read-Only Sistem)</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin (Full CRUD Akses)</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-medium text-ink mb-1.5">Status Akun <span class="text-error font-bold">*</span></label>
                    <select id="status" name="status" required class="edudash-select">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                <button type="submit" class="edudash-btn-primary text-sm">Simpan Pengguna</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
