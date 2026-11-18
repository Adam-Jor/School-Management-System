<div x-data="{
        toasts: [],
        addToast(message, type = 'success', undoUrl = null, undoToken = null, duration = 6000) {
            const id = Date.now() + Math.random().toString(36).substring(2, 5);
            const toast = {
                id,
                message,
                type,
                undoUrl,
                undoToken,
                duration,
                progress: 100,
                timer: null,
                interval: null
            };

            this.toasts.push(toast);

            const startTime = Date.now();
            toast.interval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                toast.progress = Math.max(0, 100 - (elapsed / duration) * 100);
            }, 50);

            toast.timer = setTimeout(() => {
                this.removeToast(id);
            }, duration);
        },
        removeToast(id) {
            const idx = this.toasts.findIndex(t => t.id === id);
            if (idx !== -1) {
                const toast = this.toasts[idx];
                clearTimeout(toast.timer);
                clearInterval(toast.interval);
                this.toasts.splice(idx, 1);
            }
        },
        triggerUndo(toast) {
            if (!toast.undoUrl) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = toast.undoUrl;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = toast.undoToken || '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
     }"
     x-init="
        @if (session('toast'))
            addToast(
                '{{ addslashes(rtrim(session('toast'), '!. ')) }}',
                '{{ session('toast_type', 'success') }}',
                '{{ session('toast_undo_url', '') }}',
                '{{ csrf_token() }}'
            );
        @endif
     "
     @notify.window="addToast($event.detail.message, $event.detail.type || 'success', $event.detail.undoUrl || null, $event.detail.undoToken || null, $event.detail.duration || 6000)"
     class="fixed top-4 right-4 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="pointer-events-auto bg-surface border border-line rounded-xl p-3.5 shadow-xl flex flex-col gap-2 overflow-hidden relative">

            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-2.5 min-w-0">
                    <!-- Icon based on type -->
                    <template x-if="toast.type === 'success'">
                        <span class="size-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-heroicon-o-check-circle class="size-4" />
                        </span>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <span class="size-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <x-heroicon-o-exclamation-triangle class="size-4" />
                        </span>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <span class="size-6 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <x-heroicon-o-x-circle class="size-4" />
                        </span>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <span class="size-6 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                            <x-heroicon-o-information-circle class="size-4" />
                        </span>
                    </template>

                    <div class="min-w-0 pt-0.5">
                        <p class="text-xs font-medium text-ink" x-text="toast.message"></p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <!-- Undo Button if available -->
                    <template x-if="toast.undoUrl">
                        <button type="button"
                                @click="triggerUndo(toast)"
                                class="px-2 py-1 rounded bg-raised hover:bg-line text-[11px] font-semibold text-primary transition-colors">
                            Urungkan
                        </button>
                    </template>

                    <!-- Dismiss -->
                    <button type="button"
                            @click="removeToast(toast.id)"
                            aria-label="Tutup notifikasi"
                            class="text-muted hover:text-ink transition-colors p-0.5">
                        <x-heroicon-o-x-mark class="size-4" />
                    </button>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="w-full bg-line/50 h-1 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-75"
                     :class="{
                         'bg-emerald-500': toast.type === 'success',
                         'bg-amber-500': toast.type === 'warning',
                         'bg-rose-500': toast.type === 'error',
                         'bg-sky-500': toast.type === 'info'
                     }"
                     :style="`width: ${toast.progress}%`"></div>
            </div>
        </div>
    </template>
</div>
