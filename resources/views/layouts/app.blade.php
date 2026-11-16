@props([
    'title' => 'Dashboard',
    'breadcrumbs' => [],
])
<!DOCTYPE html>
<html lang="id" data-theme="edudash">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — EDUDASH School Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-page text-ink antialiased min-h-screen selection:bg-primary selection:text-white"
      x-data="{
          mobileSidebarOpen: false,
          archiveModal: { open: false, action: '', name: '', code: '' },
          openArchiveModal(action, name, code = '') {
              this.archiveModal = { open: true, action, name, code };
          }
      }">

    <!-- Toast Notification -->
    @if (session('toast'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3500)"
             x-show="show"
             x-transition.opacity.duration.150ms
             class="fixed top-4 right-4 z-50">
            <div class="bg-surface border border-line rounded-xl px-4 py-3 shadow-card flex items-center gap-3">
                <span class="size-2 rounded-full bg-success shrink-0"></span>
                <span class="text-xs font-medium text-ink">{{ rtrim(session('toast'), '!.') }}</span>
                <button type="button"
                        @click="show = false"
                        aria-label="Tutup notifikasi"
                        class="text-muted hover:text-ink transition-colors duration-150">
                    <x-heroicon-o-x-mark class="size-4 stroke-[1.5]" />
                </button>
            </div>
        </div>
    @endif

    <div class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen"
             x-transition.opacity.duration.150ms
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 bg-ink/30 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar (240px desktop, drawer on mobile) -->
        <x-sidebar />

        <!-- Main Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-[240px]">
            <!-- Topbar -->
            <x-topbar :breadcrumbs="$breadcrumbs" />

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-8 max-w-[1440px] w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Global Archive Confirmation Modal -->
    <x-modal-confirm />

    @livewireScripts
    @stack('scripts')
</body>
</html>
