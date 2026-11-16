<x-layouts.app title="Edit Pengguna" :breadcrumbs="[['label' => 'Data Pengguna', 'url' => route('users.index')], ['label' => 'Edit: ' . $user->name]]">
    <x-page-header :title="'Edit Pengguna — ' . $user->name" :subtitle="'Perbarui akun ' . $user->email . ' (' . strtoupper($user->role) . ').'" />

    <x-card class="max-w-[720px]">
        <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-medium text-ink mb-1.5">Nama Lengkap <span class="text-error font-bold">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus required class="edudash-input" />
                    @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="username" class="block text-xs font-medium text-ink mb-1.5">Username <span class="text-error font-bold">*</span></label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required class="edudash-input" />
                    @error('username') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-ink mb-1.5">Alamat Email <span class="text-error font-bold">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="edudash-input" />
                    @error('email') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-ink mb-1.5">Kata Sandi Baru (Opsional)</label>
                    <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah…" class="edudash-input" />
                    @error('password') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role" class="block text-xs font-medium text-ink mb-1.5">Role Hak Akses <span class="text-error font-bold">*</span></label>
                    <select id="role" name="role" required class="edudash-select">
                        <option value="student" @selected(old('role', $user->role) === 'student')>Student</option>
                        <option value="teacher" @selected(old('role', $user->role) === 'teacher')>Teacher</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-medium text-ink mb-1.5">Status Akun <span class="text-error font-bold">*</span></label>
                    <select id="status" name="status" required class="edudash-select">
                        <option value="Aktif" @selected(old('status', $user->status) === 'Aktif')>Aktif</option>
                        <option value="Nonaktif" @selected(old('status', $user->status) === 'Nonaktif')>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                <button type="submit" class="edudash-btn-primary text-sm">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
