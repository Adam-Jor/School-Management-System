<x-layouts.app title="Edit Data Siswa" :breadcrumbs="[['label' => 'Data Siswa', 'url' => route('students.index')], ['label' => 'Edit: ' . $student->name]]">
    <x-page-header
        :title="'Edit Data Siswa — ' . $student->name"
        :subtitle="'Perbarui data siswa ' . $student->student_code . ' (' . ($student->schoolClass?->name ?? '-') . ').'" />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Area Upload Foto Siswa -->
                <div class="bg-sidebar border border-line rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <x-avatar :name="$student->name" :src="$student->img_url" size="lg" />
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-ink">Foto Profil Siswa</p>
                        <p class="text-xs text-muted mt-0.5">
                            Format JPG, PNG, atau WEBP · Maksimal 2 MB · Otomatis kompres ke WebP
                        </p>
                        <input type="file"
                               name="photo"
                               accept=".jpg,.jpeg,.png,.webp"
                               class="mt-2.5 block w-full text-xs text-muted file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border file:border-primary file:bg-surface file:text-primary file:font-semibold hover:file:bg-primary-soft" />
                        @error('photo')
                            <p class="text-[11px] font-medium text-error mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Grid 2 Kolom Input Pill -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="student_code" class="block text-xs font-medium text-ink mb-1.5">
                            Kode Siswa <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="student_code"
                               name="student_code"
                               value="{{ old('student_code', $student->student_code) }}"
                               placeholder="Contoh: STD-001"
                               autofocus
                               required
                               class="edudash-input {{ $errors->has('student_code') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('student_code')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-medium text-ink mb-1.5">
                            Nama Lengkap Siswa <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $student->name) }}"
                               placeholder="Masukkan nama lengkap siswa…"
                               required
                               class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('name')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="class_id" class="block text-xs font-medium text-ink mb-1.5">
                            Kelas (<code class="text-[11px]">tbl_classes</code>) <span class="text-error font-bold">*</span>
                        </label>
                        <select id="class_id" name="class_id" required class="edudash-select">
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" @selected(old('class_id', $student->class_id) == $cls->id)>
                                    {{ $cls->name }} — {{ $cls->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user_id" class="block text-xs font-medium text-ink mb-1.5">
                            Akun Pengguna (<code class="text-[11px]">tbl_users</code>)
                        </label>
                        <select id="user_id" name="user_id" class="edudash-select">
                            <option value="">Tanpa akun terkait</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id', $student->user_id) == $u->id)>
                                    {{ $u->email }} ({{ $u->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-medium text-ink mb-1.5">
                            Status Siswa <span class="text-error font-bold">*</span>
                        </label>
                        <select id="status" name="status" required class="edudash-select">
                            <option value="Aktif" @selected(old('status', $student->status) === 'Aktif')>● Aktif</option>
                            <option value="Menunggu" @selected(old('status', $student->status) === 'Menunggu')>● Menunggu</option>
                            <option value="Pindah" @selected(old('status', $student->status) === 'Pindah')>● Pindah</option>
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-medium text-ink mb-1.5">
                            Nomor Telepon Wali
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $student->phone) }}"
                               placeholder="Contoh: 081234567890"
                               class="edudash-input {{ $errors->has('phone') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('phone')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-medium text-ink mb-1.5">
                        Alamat Lengkap / Catatan Siswa
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="3"
                              placeholder="Masukkan alamat lengkap siswa…"
                              class="edudash-textarea">{{ old('address', $student->address) }}</textarea>
                </div>

                <!-- Opsi Archive di Bagian Bawah (design.md Bagian 5) -->
                <div class="bg-[#FEF2F2] border border-error-soft rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-[#B91C1C]">Arsipkan Siswa (Soft Delete)</p>
                        <p class="text-[11px] text-muted mt-0.5">
                            Memindahkan data siswa ke Archive (<code class="text-ink">archived = 1</code>) tanpa menghapus riwayat nilai.
                        </p>
                    </div>
                    <button type="button"
                            @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                            class="px-4 py-2 rounded-full bg-error-soft border border-error text-[#B91C1C] text-xs font-semibold hover:bg-error hover:text-white transition">
                        🗄 Archive Siswa
                    </button>
                </div>

                <!-- Footer Tombol Batal & Simpan -->
                <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3">
                    <span class="text-xs text-muted">Diperbarui: {{ format_date($student->updated_at, true) }}</span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('students.index') }}" wire:navigate class="edudash-btn-secondary text-sm">
                            Batal
                        </a>
                        <button type="submit" class="edudash-btn-primary text-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        <x-card class="lg:col-span-4 space-y-3">
            <h2 class="text-base font-semibold text-ink">Ringkasan Profil Siswa</h2>
            <div class="flex items-center gap-3 pt-1">
                <x-avatar :name="$student->name" :src="$student->img_url" size="lg" />
                <div>
                    <p class="text-sm font-bold text-ink">{{ $student->name }}</p>
                    <p class="text-xs text-muted">{{ $student->student_code }} · {{ $student->schoolClass?->name ?? '-' }}</p>
                    <div class="mt-1.5">
                        <x-status-badge :status="$student->status" />
                    </div>
                </div>
            </div>
            <div class="pt-3 border-t border-line text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-muted">Slug URL:</span>
                    <span class="font-mono text-ink">{{ $student->slug }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Terdaftar:</span>
                    <span class="text-ink font-medium">{{ format_date($student->created_at) }}</span>
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
