<div x-data="{
        open: false,
        title: '',
        subtitle: '',
        badge: '',
        meta: [],
        actions: [],
        contentHtml: '',
        openDrawer(e) {
            const data = e.detail || {};
            this.title = data.title || 'Detail Data';
            this.subtitle = data.subtitle || '';
            this.badge = data.badge || '';
            this.meta = data.meta || [];
            this.actions = data.actions || [];
            this.contentHtml = data.contentHtml || '';
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        closeDrawer() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        }
     }"
     @open-drawer.window="openDrawer($event)"
     @keydown.escape.window="closeDrawer()"
     class="relative z-50">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeDrawer()"
         class="fixed inset-0 bg-ink/40 backdrop-blur-sm"
         style="display: none;"></div>

    <!-- Slide-Over Desktop & Bottom Sheet Mobile -->
    <div x-show="open"
         x-transition:enter="transform transition ease-in-out duration-250"
         x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full"
         x-transition:enter-end="translate-y-0 sm:translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-200"
         x-transition:leave-start="translate-y-0 sm:translate-x-0"
         x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full"
         class="fixed inset-x-0 bottom-0 sm:inset-y-0 sm:left-auto sm:right-0 w-full sm:max-w-md lg:max-w-lg bg-surface border-t sm:border-t-0 sm:border-l border-line shadow-2xl rounded-t-3xl sm:rounded-none z-50 flex flex-col max-h-[85vh] sm:max-h-full overflow-hidden"
         style="display: none;">

        <!-- Mobile Sheet Drag Handle Bar -->
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1">
            <div class="w-12 h-1.5 rounded-full bg-line"></div>
        </div>

        <!-- Header -->
        <div class="px-5 py-4 border-b border-line flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-semibold text-ink truncate" x-text="title"></h3>
                    <template x-if="badge">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-primary-soft text-primary" x-text="badge"></span>
                    </template>
                </div>
                <p class="text-xs text-muted truncate mt-0.5" x-text="subtitle"></p>
            </div>
            <button type="button"
                    @click="closeDrawer()"
                    class="size-8 rounded-lg border border-line text-secondary hover:bg-raised flex items-center justify-center shrink-0">
                <x-heroicon-o-x-mark class="size-4" />
            </button>
        </div>

        <!-- Body / Content -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <!-- Meta Key-Value Pairs -->
            <template x-if="meta.length > 0">
                <div class="rounded-xl border border-line bg-sidebar p-3.5 divide-y divide-line/60">
                    <template x-for="(item, idx) in meta" :key="idx">
                        <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between gap-3 text-xs">
                            <span class="text-muted" x-text="item.label"></span>
                            <span class="font-medium text-ink text-right truncate" x-text="item.value"></span>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Custom HTML Content if injected -->
            <div x-html="contentHtml"></div>
        </div>

        <!-- Footer Actions -->
        <template x-if="actions.length > 0">
            <div class="px-5 py-3.5 bg-sidebar border-t border-line flex items-center justify-end gap-2.5">
                <template x-for="(action, idx) in actions" :key="idx">
                    <a :href="action.url"
                       :class="action.class || 'edudash-btn-secondary text-xs h-9 px-3'"
                       x-text="action.label"></a>
                </template>
            </div>
        </template>
    </div>
</div>
