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

    <!-- Toast Notification (design.md 4.10: hilang otomatis 3 dtk via Alpine) -->
    @if (session('toast'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3500)"
             x-show="show"
             x-transition.duration.200ms
             class="toast toast-top toast-end z-50 mt-3 mr-4">
            <div class="alert bg-success-soft border border-success text-[#15803D] rounded-full px-5 py-2.5 shadow-lg flex items-center gap-2.5">
                <span class="size-5 rounded-full bg-success text-white flex items-center justify-center text-xs font-bold">✓</span>
                <span class="text-xs font-semibold">{{ session('toast') }}</span>
                <button type="button" @click="show = false" aria-label="Tutup notifikasi" class="text-[#15803D]/70 hover:text-[#15803D] ml-1">✕</button>
            </div>
        </div>
    @endif

    <div class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen"
             x-transition.opacity
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 bg-ink/30 backdrop-blur-xs z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar (240px desktop, drawer on mobile) -->
        <x-sidebar />

        <!-- Main Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-[240px]">
            <!-- Topbar -->
            <x-topbar :breadcrumbs="$breadcrumbs" />

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-8 max-w-[1400px] w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Global Archive Confirmation Modal (design.md 4.10) -->
    <x-modal-confirm />

    @livewireScripts
    @stack('scripts')
</body>
</html>
