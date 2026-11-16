<x-layouts.app title="Tambah Data Siswa" :breadcrumbs="[['label' => 'Data Siswa', 'url' => route('students.index')], ['label' => 'Tambah Siswa']]">
    <x-page-header
        title="Tambah Data Siswa"
        subtitle="Formulir pendaftaran siswa baru dan penempatan rombongan belajar" />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- Upload Foto Siswa -->
                <div class="bg-sidebar border border-line rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <x-avatar name="Siswa Baru" size="lg" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-ink">Foto Profil Siswa (WebP)</p>
                        <p class="text-xs text-muted mt-0.5">
                            JPG, PNG, atau WEBP maksimal 2 MB · Disimpan otomatis sebagai <code class="font-mono text-ink">YmdHis-nama.webp</code>
                        </p>
                        <input type="file"
                               name="photo"
                               accept=".jpg,.jpeg,.png,.webp"
                               class="mt-2.5 block w-full text-xs text-secondary file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-line file:bg-surface file:text-ink file:font-medium hover:file:bg-raised" />
                        @error('photo')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Grid 2 Kolom -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="student_code" class="block text-xs font-normal text-secondary mb-1.5">
                            Kode siswa <span class="text-error">*</span>
                        </label>
                        <input type="text"
                               id="student_code"
                               name="student_code"
                               value="{{ old('student_code', $nextCode) }}"
                               placeholder="STD-013"
                               autofocus
                               required
                               class="edudash-input font-mono {{ $errors->has('student_code') ? 'border-error bg-error-soft/20' : '' }}" />
                        @error('student_code')
                            <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-normal text-secondary mb-1.5">
                            Nama lengkap siswa <span class="text-error">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Nama lengkap sesuai ijazah"
                               required
                               class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/20' : '' }}" />
                        @error('name')
                            <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="class_id" class="block text-xs font-normal text-secondary mb-1.5">
                            Kelas penempatan <span class="text-error">*</span>
                        </label>
                        <select id="class_id"
                                name="class_id"
                                required
                                class="edudash-select {{ $errors->has('class_id') ? 'border-error bg-error-soft/20' : '' }}">
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" @selected(old('class_id') == $cls->id)>
                                    {{ $cls->name }} ({{ $cls->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user_id" class="block text-xs font-normal text-secondary mb-1.5">
                            Akun pengguna terkait
                        </label>
                        <select id="user_id"
                                name="user_id"
                                class="edudash-select {{ $errors->has('user_id') ? 'border-error bg-error-soft/20' : '' }}">
                            <option value="">Buat akun siswa otomatis</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                                    {{ $u->email }} ({{ $u->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-normal text-secondary mb-1.5">
                            Status registrasi <span class="text-error">*</span>
                        </label>
                        <select id="status"
                                name="status"
                                required
                                class="edudash-select {{ $errors->has('status') ? 'border-error bg-error-soft/20' : '' }}">
                            <option value="Aktif" @selected(old('status', 'Aktif') === 'Aktif')>Aktif</option>
                            <option value="Menunggu" @selected(old('status') === 'Menunggu')>Menunggu</option>
                            <option value="Pindah" @selected(old('status') === 'Pindah')>Pindah</option>
                        </select>
                        @error('status')
                            <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-normal text-secondary mb-1.5">
                            Nomor telepon wali
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="081234567890"
                               class="edudash-input tabular-nums {{ $errors->has('phone') ? 'border-error bg-error-soft/20' : '' }}" />
                        @error('phone')
                            <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-normal text-secondary mb-1.5">
                        Alamat domisili siswa
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="3"
                              placeholder="Alamat lengkap tempat tinggal siswa"
                              class="edudash-textarea">{{ old('address') }}</textarea>
                </div>

                <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-muted">
                        Kolom bertanda <span class="text-error">*</span> wajib diisi
                    </p>
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('students.index') }}" wire:navigate class="edudash-btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="edudash-btn-primary">
                            Simpan Siswa
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        <x-card class="lg:col-span-4 space-y-3">
            <h2 class="text-sm font-medium text-ink">Ketentuan Input</h2>
            <p class="text-xs text-secondary leading-relaxed">
                Setiap entri siswa baru akan tersimpan ke direktori sekolah serta dihubungkan ke rombongan belajar dan akun login siswa.
            </p>
        </x-card>
    </div>
</x-layouts.app>
