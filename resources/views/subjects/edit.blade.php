<x-layouts.app title="Edit Mata Pelajaran" :breadcrumbs="[['label' => 'Mata Pelajaran', 'url' => route('subjects.index')], ['label' => 'Edit: ' . $subject->name]]">
    <x-page-header :title="'Edit Mata Pelajaran — ' . $subject->name" :subtitle="'Perbarui informasi mata pelajaran ' . $subject->code . '.'" />

    <x-card class="max-w-[720px]">
        <form action="{{ route('subjects.update', $subject) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="code" class="block text-xs font-medium text-ink mb-1.5">Kode Mapel <span class="text-error font-bold">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $subject->code) }}" autofocus required
                           class="edudash-input {{ $errors->has('code') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('code') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-xs font-medium text-ink mb-1.5">Nama Mata Pelajaran <span class="text-error font-bold">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" required
                           class="edudash-input {{ $errors->has('name') ? 'border-error bg-error-soft/30' : '' }}" />
                    @error('name') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="credit_hours" class="block text-xs font-medium text-ink mb-1.5">Beban Jam Pelajaran (JP) <span class="text-error font-bold">*</span></label>
                    <input type="number" id="credit_hours" name="credit_hours" value="{{ old('credit_hours', $subject->credit_hours) }}" min="1" max="12" required class="edudash-input" />
                    @error('credit_hours') <p class="text-[11px] font-medium text-error mt-1.5 pl-3">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-ink mb-1.5">Deskripsi Silabus</label>
                <textarea id="description" name="description" rows="3" class="edudash-textarea">{{ old('description', $subject->description) }}</textarea>
            </div>

            <div class="bg-[#FEF2F2] border border-error-soft rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-[#B91C1C]">Arsipkan Mata Pelajaran</p>
                    <p class="text-[11px] text-muted mt-0.5">Pindahkan mata pelajaran ke arsip tanpa menghapus riwayat nilai.</p>
                </div>
                <button type="button"
                        @click="openArchiveModal('{{ route('subjects.archive', $subject) }}', '{{ addslashes($subject->name) }}', '{{ $subject->code }}')"
                        class="px-4 py-2 rounded-full bg-error-soft border border-error text-[#B91C1C] text-xs font-semibold hover:bg-error hover:text-white transition">
                    Archive Mapel
                </button>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('subjects.index') }}" wire:navigate class="edudash-btn-secondary text-sm">Batal</a>
                <button type="submit" class="edudash-btn-primary text-sm">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
