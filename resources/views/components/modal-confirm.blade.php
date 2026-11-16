<div x-show="archiveModal.open"
     x-transition.opacity.duration.150ms
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-archive-title">
    <div @click.outside="archiveModal.open = false"
         class="bg-surface rounded-2xl p-6 max-w-md w-full shadow-xl border border-line">
        <div class="flex items-start gap-3.5">
            <div class="size-10 rounded-lg bg-error-soft text-error flex items-center justify-center shrink-0">
                <x-heroicon-o-archive-box class="size-5 stroke-[1.5]" />
            </div>
            <div>
                <h3 id="modal-archive-title" class="text-base font-medium text-ink">Arsipkan data ini?</h3>
                <p class="text-xs font-mono text-muted mt-1" x-text="archiveModal.name + (archiveModal.code ? ' · ' + archiveModal.code : '')"></p>
            </div>
        </div>

        <p class="text-xs text-secondary leading-relaxed mt-4">
            Data ini akan dipindahkan ke arsip dan disembunyikan dari daftar aktif. Anda dapat memulihkan data ini kapan saja melalui menu Arsip Data.
        </p>

        <form :action="archiveModal.action" method="POST" class="mt-6 flex items-center justify-end gap-2">
            @csrf
            @method('PATCH')
            <button type="button"
                    @click="archiveModal.open = false"
                    class="edudash-btn-secondary h-9 px-4 text-xs">
                Batal
            </button>
            <button type="submit"
                    class="h-9 inline-flex items-center justify-center rounded-lg px-4 bg-error hover:bg-red-700 text-white font-medium text-xs transition-colors duration-150">
                Arsipkan data
            </button>
        </form>
    </div>
</div>
