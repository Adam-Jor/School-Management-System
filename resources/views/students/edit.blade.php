<x-layouts.app title="Edit Data Siswa" :breadcrumbs="[['label' => 'Data Siswa', 'url' => route('students.index')], ['label' => $student->name]]">
    <x-page-header
        :title="'Edit Siswa — ' . $student->name"
        :subtitle="'Perbarui data registrasi ' . $student->student_code . ' (' . ($student->schoolClass?->name ?? '-') . ')'" />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="bg-sidebar border border-line rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <x-avatar :name="$student->name" :src="$student->img_url" size="lg" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-ink">Foto Profil Siswa</p>
                        <p class="text-xs text-muted mt-0.5">JPG, PNG, atau WEBP maksimal 2 MB</p>
                        <input type="file"
                               name="photo"
                               accept=".jpg,.jpeg,.png,.webp"
                               class="mt-2.5 block w-full text-xs text-secondary file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-line file:bg-surface file:text-ink file:font-medium hover:file:bg-raised" />
                        @error('photo')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="student_code" class="block text-xs font-normal text-secondary mb-1.5">
                            Kode siswa <span class="text-error">*</span>
                        </label>
                        <input type="text"
                               id="student_code"
                               name="student_code"
                               value="{{ old('student_code', $student->student_code) }}"
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
                               value="{{ old('name', $student->name) }}"
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
                        <select id="class_id" name="class_id" required class="edudash-select">
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" @selected(old('class_id', $student->class_id) == $cls->id)>
                                    {{ $cls->name }} ({{ $cls->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="user_id" class="block text-xs font-normal text-secondary mb-1.5">
                            Akun pengguna terkait
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
                        <label for="status" class="block text-xs font-normal text-secondary mb-1.5">
                            Status registrasi <span class="text-error">*</span>
                        </label>
                        <select id="status" name="status" required class="edudash-select">
                            <option value="Aktif" @selected(old('status', $student->status) === 'Aktif')>Aktif</option>
                            <option value="Menunggu" @selected(old('status', $student->status) === 'Menunggu')>Menunggu</option>
                            <option value="Pindah" @selected(old('status', $student->status) === 'Pindah')>Pindah</option>
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-normal text-secondary mb-1.5">
                            Nomor telepon wali
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $student->phone) }}"
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
                              class="edudash-textarea">{{ old('address', $student->address) }}</textarea>
                </div>

                <div class="bg-error-soft/40 border border-error/20 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-[#B91C1C]">Arsipkan Siswa</p>
                        <p class="text-xs text-secondary mt-0.5">
                            Sembunyikan siswa dari daftar aktif (<code class="font-mono text-ink">archived = 1</code>) tanpa menghapus riwayat nilai
                        </p>
                    </div>
                    <button type="button"
                            @click="openArchiveModal('{{ route('students.archive', $student) }}', '{{ addslashes($student->name) }}', '{{ $student->student_code }}')"
                            class="h-8 px-3 rounded-lg bg-surface border border-error/30 text-[#B91C1C] text-xs font-medium hover:bg-error hover:text-white transition-colors duration-150 inline-flex items-center gap-1.5">
                        <x-heroicon-o-archive-box class="size-4 stroke-[1.5]" />
                        <span>Arsipkan Siswa</span>
                    </button>
                </div>

                <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3">
                    <span class="font-mono text-xs text-muted tabular-nums">Diperbarui {{ format_date($student->updated_at, true) }}</span>
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('students.index') }}" wire:navigate class="edudash-btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="edudash-btn-primary">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        <x-card class="lg:col-span-4 space-y-3">
            <h2 class="text-sm font-medium text-ink">Identitas Registrasi</h2>
            <div class="flex items-center gap-3 pt-1">
                <x-avatar :name="$student->name" :src="$student->img_url" size="lg" />
                <div>
                    <p class="text-sm font-medium text-ink">{{ $student->name }}</p>
                    <p class="font-mono text-xs text-muted mt-0.5">{{ $student->student_code }} · {{ $student->schoolClass?->name ?? '-' }}</p>
                    <div class="mt-2">
                        <x-status-badge :status="$student->status" />
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
