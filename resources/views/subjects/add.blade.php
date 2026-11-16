<x-layouts.app title="Tambah Mata Pelajaran" :breadcrumbs="[['label' => 'Mata Pelajaran', 'url' => route('subjects.index')], ['label' => 'Tambah Mapel']]">
    <x-page-header title="Tambah Mata Pelajaran" subtitle="Tambahkan mata pelajaran baru ke tbl_subjects." />

    <x-card class="max-w-[720px]">
        <form action="{{ route('subjects.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="code" class="block text-xs font-medium text-ink mb-1.5">Kode Mapel <span class="text-error font-bold">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $nextCode) }}" placeholder="Contoh: MP-009" autofocus required
                           class="edudash-input {{ $errors->has('code') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('code') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-xs font-medium text-ink mb-1.5">Nama Mata Pelajaran <span class="text-error font-bold">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Sejarah Indonesia" required
                           class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="credit_hours" class="block text-xs font-medium text-ink mb-1.5">Beban Jam Pelajaran (JP) <span class="text-error font-bold">*</span></label>
                    <input type="number" id="credit_hours" name="credit_hours" value="{{ old('credit_hours', 4) }}" min="1" max="12" required class="edudash-input" />
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-ink mb-1.5">Deskripsi Silabus</label>
                <textarea id="description" name="description" rows="3" placeholder="Masukkan deskripsi singkat capaian pembelajaran…" class="edudash-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('subjects.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                <button type="submit" class="edudash-btn-primary text-sm">Simpan Mapel</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
