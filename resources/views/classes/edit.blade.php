<x-layouts.app title="Edit Kelas" :breadcrumbs="[['label' => 'Data Kelas', 'url' => route('classes.index')], ['label' => 'Edit: ' . $schoolClass->name]]">
    <x-page-header :title="'Edit Kelas — ' . $schoolClass->name" :subtitle="'Perbarui informasi kelas ' . $schoolClass->code . '.'" />

    <x-card class="max-w-[720px]">
        <form action="{{ route('classes.update', $schoolClass) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="code" class="block text-xs font-medium text-ink mb-1.5">Kode Kelas <span class="text-error font-bold">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $schoolClass->code) }}" autofocus required
                           class="edudash-input {{ $errors->has('code') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('code') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-xs font-medium text-ink mb-1.5">Nama Kelas <span class="text-error font-bold">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $schoolClass->name) }}" required
                           class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="capacity" class="block text-xs font-medium text-ink mb-1.5">Kapasitas Siswa <span class="text-error font-bold">*</span></label>
                    <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $schoolClass->capacity) }}" min="10" max="60" required class="edudash-input" />
                    @error('capacity') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-ink mb-1.5">Deskripsi Kelas</label>
                <textarea id="description" name="description" rows="3" class="edudash-textarea">{{ old('description', $schoolClass->description) }}</textarea>
            </div>

            <div class="bg-[#FEF2F2] border border-error-soft rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-[#B91C1C]">Arsipkan Kelas</p>
                    <p class="text-[11px] text-muted mt-0.5">Pindahkan kelas ke arsip sistem tanpa menghapus riwayat siswa.</p>
                </div>
                <button type="button"
                        @click="openArchiveModal('{{ route('classes.archive', $schoolClass) }}', '{{ addslashes($schoolClass->name) }}', '{{ $schoolClass->code }}')"
                        class="px-4 py-2 rounded-full bg-error-soft border border-error text-[#B91C1C] text-xs font-semibold hover:bg-error hover:text-white transition">
                    Archive Kelas
                </button>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('classes.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                <button type="submit" class="edudash-btn-primary text-sm">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
