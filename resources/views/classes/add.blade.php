<x-layouts.app title="Tambah Kelas" :breadcrumbs="[['label' => 'Data Kelas', 'url' => route('classes.index')], ['label' => 'Tambah Kelas']]">
    <x-page-header title="Tambah Data Kelas" subtitle="Tambahkan data rombongan belajar baru." />

    <x-card class="max-w-[720px]">
        <form action="{{ route('classes.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="code" class="block text-xs font-medium text-ink mb-1.5">
                        Kode Kelas <span class="text-error font-bold">*</span>
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code', $nextCode) }}" placeholder="Contoh: CLS-10A3" autofocus required
                           class="edudash-input {{ $errors->has('code') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('code') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-xs font-medium text-ink mb-1.5">
                        Nama Kelas <span class="text-error font-bold">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: 10 IPA 3" required
                           class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="capacity" class="block text-xs font-medium text-ink mb-1.5">
                        Kapasitas Siswa <span class="text-error font-bold">*</span>
                    </label>
                    <input type="number" id="capacity" name="capacity" value="{{ old('capacity', 36) }}" min="10" max="60" required
                           class="edudash-input" />
                    @error('capacity') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-ink mb-1.5">Deskripsi Kelas</label>
                <textarea id="description" name="description" rows="3" placeholder="Masukkan keterangan lokasi ruang atau peminatan kelas…" class="edudash-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('classes.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                <button type="submit" class="edudash-btn-primary text-sm">Simpan Kelas</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
