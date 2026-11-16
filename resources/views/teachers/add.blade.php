<x-layouts.app title="Tambah Data Guru" :breadcrumbs="[['label' => 'Data Guru', 'url' => route('teachers.index')], ['label' => 'Tambah Guru']]">
    <x-page-header
        title="Tambah Data Guru"
        subtitle="Daftarkan guru pengajar baru dan hubungkan ke mata pelajaran (tbl_subjects) serta akun pengguna (tbl_users)." />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <form action="{{ route('teachers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="bg-sidebar border border-line rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <x-avatar name="Guru Baru" size="lg" />
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-ink">Foto Profil Guru (public/uploads/teachers/)</p>
                        <p class="text-xs text-muted mt-0.5">
                            Format JPG, PNG, atau WEBP · Maksimal 2 MB · Otomatis kompres ke WebP
                        </p>
                        <input type="file"
                               name="photo"
                               accept=".jpg,.jpeg,.png,.webp"
                               class="mt-2.5 block w-full text-xs text-muted file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border file:border-primary file:bg-surface file:text-primary file:font-semibold hover:file:bg-primary-soft" />
                        @error('photo')
                            <p class="text-[11px] font-medium text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="teacher_code" class="block text-xs font-medium text-ink mb-1.5">
                            Kode Guru (<code class="text-[11px]">teacher_code</code>) <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="teacher_code"
                               name="teacher_code"
                               value="{{ old('teacher_code', $nextCode) }}"
                               placeholder="Contoh: TCH-009"
                               autofocus
                               required
                               class="edudash-input {{ $errors->has('teacher_code') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('teacher_code')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-medium text-ink mb-1.5">
                            Nama Lengkap & Gelar Guru <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Contoh: Budi Santoso, S.Pd"
                               required
                               class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('name')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject_id" class="block text-xs font-medium text-ink mb-1.5">
                            Mata Pelajaran (<code class="text-[11px]">FK subject_id</code>) <span class="text-error font-bold">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" required class="edudash-select">
                            <option value="">Pilih mata pelajaran…</option>
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}" @selected(old('subject_id') == $sub->id)>
                                    {{ $sub->code }} — {{ $sub->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user_id" class="block text-xs font-medium text-ink mb-1.5">
                            Akun Pengguna (<code class="text-[11px]">FK user_id → tbl_users</code>)
                        </label>
                        <select id="user_id" name="user_id" class="edudash-select">
                            <option value="">Buat akun teacher otomatis</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                                    {{ $u->email }} ({{ $u->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-medium text-ink mb-1.5">
                            Nomor Telepon / WhatsApp <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="Contoh: 081234567890"
                               required
                               class="edudash-input {{ $errors->has('phone') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('phone')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-medium text-ink mb-1.5">
                            Status Mengajar <span class="text-error font-bold">*</span>
                        </label>
                        <select id="status" name="status" required class="edudash-select">
                            <option value="Aktif" @selected(old('status', 'Aktif') === 'Aktif')>Aktif</option>
                            <option value="Cuti" @selected(old('status') === 'Cuti')>Cuti</option>
                            <option value="Pindah" @selected(old('status') === 'Pindah')>Pindah</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-medium text-ink mb-1.5">
                        Kualifikasi Akademik & Catatan Mengajar
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              placeholder="Contoh: Wali Kelas 10 IPA 1 · Pembina Olimpiade Matematika…"
                              class="edudash-textarea">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-muted">* Field wajib diisi (TeacherRequest Validation)</p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('teachers.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                        <button type="submit" class="edudash-btn-primary text-sm">Simpan</button>
                    </div>
                </div>
            </form>
        </x-card>

        <x-card class="lg:col-span-4 space-y-3">
            <h2 class="text-base font-semibold text-ink">Relasi Data Guru</h2>
            <p class="text-xs text-muted leading-relaxed">
                Data guru disimpan di <code class="text-ink font-medium">tbl_teachers</code> dengan foreign key ke <code class="text-ink font-medium">tbl_subjects</code> dan <code class="text-ink font-medium">tbl_users</code>.
            </p>
        </x-card>
    </div>
</x-layouts.app>
