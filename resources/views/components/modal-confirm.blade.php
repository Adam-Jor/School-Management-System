<div x-show="archiveModal.open"
     x-transition.opacity
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 backdrop-blur-xs p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-archive-title">
    <div @click.outside="archiveModal.open = false"
         class="bg-surface rounded-[20px] p-6 max-w-md w-full shadow-2xl border border-line">
        <div class="flex items-start gap-4">
            <div class="size-12 rounded-full bg-error-soft text-error flex items-center justify-center shrink-0 font-bold text-lg">
                !
            </div>
            <div>
                <h3 id="modal-archive-title" class="text-lg font-bold text-ink">Arsipkan Data Ini?</h3>
                <p class="text-xs font-medium text-muted mt-0.5" x-text="archiveModal.name + (archiveModal.code ? ' · ' + archiveModal.code : '')"></p>
            </div>
        </div>

        <p class="text-xs text-muted leading-relaxed mt-4">
            Data ini akan dipindahkan ke <strong>Archive</strong> (<code class="text-ink">archived = 1</code>) dan disembunyikan dari daftar aktif. Anda dapat memulihkan (<em>restore</em>) data ini kapan saja melalui menu Archive.
        </p>

        <form :action="archiveModal.action" method="POST" class="mt-6 flex items-center justify-end gap-2.5">
            @csrf
            @method('PATCH')
            <button type="button"
                    @click="archiveModal.open = false"
                    class="edudash-btn-secondary btn-sm px-5 text-xs">
                Batal
            </button>
            <button type="submit"
                    class="btn btn-sm rounded-full px-5 bg-error hover:bg-red-600 text-white border-none font-semibold text-xs shadow-md">
                Ya, archive
            </button>
        </form>
    </div>
</div>
