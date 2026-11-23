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
    <title>{{ $title }} — School Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Reset Stored Theme -->
    <script>
        try { localStorage.removeItem('theme'); } catch (e) { }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-page text-ink antialiased min-h-screen selection:bg-primary selection:text-white" x-data="{
          mobileSidebarOpen: false,
          archiveModal: { open: false, action: '', name: '', code: '' },
          openArchiveModal(action, name, code = '') {
              this.archiveModal = { open: true, action, name, code };
          }
      }">

    <!-- Unified Multi-Variant Toast Notification & Undo System -->
    <x-toast-container />

    <!-- Command Palette Interactive (Ctrl+K / Cmd+K) -->
    <x-command-palette />

    <!-- Slide-Over Drawer & Mobile Bottom Sheet -->
    <x-drawer />

    <div class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-transition.opacity.duration.150ms @click="mobileSidebarOpen = false"
            class="fixed inset-0 bg-ink/30 z-40 lg:hidden" style="display: none;"></div>

        <!-- Sidebar (240px desktop, drawer on mobile) -->
        <x-sidebar />

        <!-- Main Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-[240px]">
            <!-- Topbar -->
            <x-topbar :breadcrumbs="$breadcrumbs" />

            <!-- Page Content (pb-24 on mobile for sticky bottom navigation bar) -->
            <main class="flex-1 px-4 pt-4 pb-24 sm:px-6 sm:pt-6 sm:pb-24 lg:p-8 max-w-[1440px] w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Mobile-First Sticky Bottom Navigation Bar -->
    <x-mobile-bottom-nav />

    <!-- Global Archive Confirmation Modal -->
    <x-modal-confirm />

    <script>
        window.openDrawer = function (detail) {
            window.dispatchEvent(new CustomEvent('open-drawer', { detail }));
        };
    </script>

    @livewireScripts
    @stack('scripts')
</body>

</html>