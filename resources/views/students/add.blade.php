<x-layouts.app title="Tambah Data Siswa" :breadcrumbs="[['label' => 'Data Siswa', 'url' => route('students.index')], ['label' => 'Tambah Siswa']]">
    <x-page-header
        title="Tambah Data Siswa"
        subtitle="Formulir pendaftaran siswa baru dengan validasi Form Request & kompresi foto WebP." />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kartu Form Utama (design.md 4.9: lebar maksimal ±720px, grid 2 kolom) -->
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- Area Upload Foto Siswa (WebP max 2MB) -->
                <div class="bg-sidebar border border-line rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <x-avatar name="Siswa Baru" size="lg" />
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-ink">Foto Profil Siswa (Konversi Otomatis WebP)</p>
                        <p class="text-xs text-muted mt-0.5">
                            Format JPG, PNG, atau WEBP · Maksimal 2 MB · Format file: <code class="text-ink">YmdHis-nama_file.webp</code>
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
                            Kode Siswa (<code class="text-[11px]">student_code</code>) <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="student_code"
                               name="student_code"
                               value="{{ old('student_code', $nextCode) }}"
                               placeholder="Contoh: STD-013"
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
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama lengkap siswa…"
                               required
                               class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('name')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="class_id" class="block text-xs font-medium text-ink mb-1.5">
                            Kelas (<code class="text-[11px]">FK class_id → tbl_classes</code>) <span class="text-error font-bold">*</span>
                        </label>
                        <select id="class_id"
                                name="class_id"
                                required
                                class="edudash-select {{ $errors->has('class_id') ? 'border-error bg-error-soft/30' : '' }}">
                            <option value="">Pilih kelas siswa…</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" @selected(old('class_id') == $cls->id)>
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
                            Akun Pengguna (<code class="text-[11px]">FK user_id → tbl_users</code>)
                        </label>
                        <select id="user_id"
                                name="user_id"
                                class="edudash-select {{ $errors->has('user_id') ? 'border-error bg-error-soft/30' : '' }}">
                            <option value="">Buat akun student otomatis</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                                    {{ $u->email }} ({{ $u->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-medium text-ink mb-1.5">
                            Status Siswa <span class="text-error font-bold">*</span>
                        </label>
                        <select id="status"
                                name="status"
                                required
                                class="edudash-select {{ $errors->has('status') ? 'border-error bg-error-soft/30' : '' }}">
                            <option value="Aktif" @selected(old('status', 'Aktif') === 'Aktif')>● Aktif</option>
                            <option value="Menunggu" @selected(old('status') === 'Menunggu')>● Menunggu</option>
                            <option value="Pindah" @selected(old('status') === 'Pindah')>● Pindah</option>
                        </select>
                        @error('status')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-medium text-ink mb-1.5">
                            Nomor Telepon Wali
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="Contoh: 081234567890"
                               class="edudash-input {{ $errors->has('phone') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('phone')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Textarea Alamat / Catatan -->
                <div>
                    <label for="address" class="block text-xs font-medium text-ink mb-1.5">
                        Catatan Akademik / Alamat Lengkap Siswa
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="3"
                              placeholder="Masukkan alamat lengkap tempat tinggal atau catatan akademik siswa…"
                              class="edudash-textarea">{{ old('address') }}</textarea>
                </div>

                <!-- Footer Tombol Batal & Simpan -->
                <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-muted">
                        Tanda <span class="text-error font-bold">*</span> menunjukkan field wajib diisi.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('students.index') }}" wire:navigate class="edudash-btn-secondary text-sm">
                            Batal
                        </a>
                        <button type="submit" class="edudash-btn-primary text-sm">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        <!-- Panel Informasi Standar Form -->
        <x-card class="lg:col-span-4 space-y-4">
            <h2 class="text-base font-semibold text-ink">Panduan Pendaftaran Siswa</h2>
            <p class="text-xs text-muted leading-relaxed">
                Setiap data siswa tersimpan di tabel <code class="text-ink font-medium">tbl_students</code> dan otomatis berelasi dengan <code class="text-ink font-medium">tbl_classes</code> serta <code class="text-ink font-medium">tbl_users</code>.
            </p>
            <ul class="space-y-2 text-xs text-muted">
                <li class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-primary"></span>
                    <span>Input pertama memiliki <strong>autofocus</strong> otomatis.</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-success"></span>
                    <span>Foto otomatis dikonversi ke <strong>WebP</strong> via Intervention Image.</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-warning"></span>
                    <span>Jika akun user dikosongkan, sistem membuatkan akun student baru.</span>
                </li>
            </ul>
        </x-card>
    </div>
</x-layouts.app>
