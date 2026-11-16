<x-layouts.app title="Edit Data Guru" :breadcrumbs="[['label' => 'Data Guru', 'url' => route('teachers.index')], ['label' => 'Edit & Profil: ' . $teacher->name]]">
    @php
        $assignedClassIds = (array) old('class_ids', $teacher->schoolClasses->pluck('id')->all());
    @endphp

    <x-page-header
        :title="'Edit Data Guru — ' . $teacher->name"
        :subtitle="'Perbarui data pengajar dan penugasan kelas (' . $teacher->teacher_code . ').'" />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <x-card class="lg:col-span-8 max-w-[720px] w-full">
            <form action="{{ route('teachers.update', $teacher) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="bg-sidebar border border-line rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <x-avatar :name="$teacher->name" :src="$teacher->img_url" size="lg" />
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-ink">Foto Profil Guru</p>
                        <p class="text-xs text-muted mt-0.5">Format JPG, PNG, atau WEBP · Maksimal 2 MB · Kompres otomatis</p>
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
                            Kode Guru <span class="text-error font-bold">*</span>
                        </label>
                        <input type="text"
                               id="teacher_code"
                               name="teacher_code"
                               value="{{ old('teacher_code', $teacher->teacher_code) }}"
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
                               value="{{ old('name', $teacher->name) }}"
                               required
                               class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                        @error('name')
                            <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject_id" class="block text-xs font-medium text-ink mb-1.5">
                            Mata Pelajaran <span class="text-error font-bold">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" required class="edudash-select">
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}" @selected(old('subject_id', $teacher->subject_id) == $sub->id)>
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
                            Akun Pengguna Terkait
                        </label>
                        <select id="user_id" name="user_id" class="edudash-select">
                            <option value="">Tanpa akun terkait</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id', $teacher->user_id) == $u->id)>
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
                               value="{{ old('phone', $teacher->phone) }}"
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
                            <option value="Aktif" @selected(old('status', $teacher->status) === 'Aktif')>Aktif</option>
                            <option value="Cuti" @selected(old('status', $teacher->status) === 'Cuti')>Cuti</option>
                            <option value="Pindah" @selected(old('status', $teacher->status) === 'Pindah')>Pindah</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink mb-2">
                        Kelas yang Diajar (Akses Absensi Siswa)
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        @foreach ($classes as $cls)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg bg-sidebar border border-line text-xs text-ink cursor-pointer hover:bg-raised transition-colors duration-150">
                                <input type="checkbox"
                                       name="class_ids[]"
                                       value="{{ $cls->id }}"
                                       @checked(in_array($cls->id, $assignedClassIds))
                                       class="rounded border-line text-primary focus:ring-primary/20" />
                                <span class="truncate">{{ $cls->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('class_ids.*')
                        <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-medium text-ink mb-1.5">
                        Kualifikasi Akademik & Catatan Mengajar
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              class="edudash-textarea">{{ old('notes', $teacher->notes) }}</textarea>
                </div>

                <div class="bg-[#FEF2F2] border border-error-soft rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-[#B91C1C]">Arsipkan Data Guru</p>
                        <p class="text-[11px] text-muted mt-0.5">Sembunyikan guru dari daftar aktif tanpa menghapus riwayat akademik.</p>
                    </div>
                    <button type="button"
                            @click="openArchiveModal('{{ route('teachers.archive', $teacher) }}', '{{ addslashes($teacher->name) }}', '{{ $teacher->teacher_code }}')"
                            class="px-4 py-2 rounded-full bg-error-soft border border-error text-[#B91C1C] text-xs font-semibold hover:bg-error hover:text-white transition">
                        Arsipkan Guru
                    </button>
                </div>

                <div class="pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3">
                    <span class="text-xs text-muted">Diperbarui: {{ format_date($teacher->updated_at, true) }}</span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('teachers.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                        <button type="submit" class="edudash-btn-primary text-sm">Simpan</button>
                    </div>
                </div>
            </form>
        </x-card>

        <div class="lg:col-span-4 space-y-6">
            <div class="bg-surface rounded-[20px] shadow-card overflow-hidden">
                <div class="h-20 bg-primary-soft"></div>
                <div class="px-6 pb-6">
                    <div class="flex items-end justify-between -mt-9 mb-3">
                        <x-avatar :name="$teacher->name" :src="$teacher->img_url" size="lg" />
                        <x-status-badge :status="$teacher->status" />
                    </div>
                    <h2 class="text-lg font-bold text-ink">{{ $teacher->name }}</h2>
                    <p class="text-xs text-muted mt-0.5">{{ $teacher->notes ?: 'Guru Pengajar Tetap' }}</p>

                    <div class="mt-4 pt-4 border-t border-line space-y-2.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-muted">Mata Pelajaran</span>
                            <span class="font-semibold text-primary">{{ $teacher->subject?->code }} · {{ $teacher->subject?->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Email Akun</span>
                            <span class="font-medium text-ink">{{ $teacher->user?->email ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Nomor Telepon</span>
                            <span class="font-medium text-ink">{{ $teacher->phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Login Terakhir</span>
                            <span class="font-mono text-muted">{{ format_date($teacher->user?->last_login_time, true) }}</span>
                        </div>
                        <div class="pt-2 border-t border-line">
                            <span class="text-muted block mb-1.5">Kelas yang Diajar</span>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($teacher->schoolClasses as $cls)
                                    <span class="edudash-keycap">{{ $cls->name }}</span>
                                @empty
                                    <span class="text-muted">Belum ditugaskan</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
