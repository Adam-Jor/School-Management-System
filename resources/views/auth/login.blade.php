<!DOCTYPE html>
<html lang="id" data-theme="edudash">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Edudash SMA Contoh Nusantara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-page text-ink min-h-screen flex items-center justify-center p-6">

    <div class="max-w-[960px] w-full grid grid-cols-1 lg:grid-cols-12 bg-surface rounded-2xl shadow-card border border-line overflow-hidden">
        <!-- Left Product Preview Column (Asymmetric Split) -->
        <div class="lg:col-span-5 bg-sidebar border-b lg:border-b-0 lg:border-r border-line p-8 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="size-7 rounded-md bg-ink text-white font-semibold text-xs flex items-center justify-center">ED</span>
                        <span class="text-base font-semibold text-ink">Edudash</span>
                    </div>
                    <span class="edudash-keycap">TA 2026/2027</span>
                </div>

                <div class="mt-6">
                    <img src="{{ asset('img/welcome-illustration.svg') }}"
                         alt="Pratinjau Kartu Registrasi Akademik Siswa STD-001"
                         class="w-full h-auto rounded-xl" />
                </div>

                <h2 class="text-lg font-medium text-ink mt-6 leading-snug">
                    Buku induk siswa, penjadwalan pengajar, dan rekap akademik dalam satu registri sekolah
                </h2>
                <p class="text-xs text-secondary mt-2 leading-relaxed">
                    Dirancang untuk tata usaha, wali kelas, dan siswa SMA Contoh Nusantara dengan pembatasan akses per peran.
                </p>
            </div>

            <div class="mt-8 pt-4 border-t border-line flex items-center justify-between text-xs text-muted tabular-nums">
                <span>SMA Contoh Nusantara</span>
                <span class="font-mono">Bandung · NPSN 20219401</span>
            </div>
        </div>

        <!-- Right Login Form Column -->
        <div class="lg:col-span-7 p-8 lg:p-10 flex flex-col justify-center"
             x-data="{
                 fillDemo(email) {
                     this.$refs.emailInput.value = email;
                     this.$refs.passwordInput.value = 'password';
                     this.$refs.emailInput.focus();
                 }
             }">
            <div>
                <p class="text-xs font-mono text-muted">PORTAL AKADEMIK TERPADU</p>
                <h1 class="text-2xl font-medium tracking-[-0.01em] text-ink mt-1.5">
                    Masuk ke Registri Akademik
                </h1>
                <p class="text-xs text-secondary mt-1">
                    Masuk menggunakan email atau username akun sekolah Anda
                </p>
            </div>

            @if (session('toast'))
                <div class="mt-4 px-3.5 py-2.5 rounded-lg bg-success-soft text-[#15803D] text-xs font-medium">
                    {{ rtrim(session('toast'), '!.') }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-normal text-secondary mb-1.5">
                        Email atau username <span class="text-error">*</span>
                    </label>
                    <input type="text"
                           id="email"
                           name="email"
                           x-ref="emailInput"
                           value="{{ old('email', 'admin@edudash.sch.id') }}"
                           placeholder="nama@edudash.sch.id"
                           autofocus
                           required
                           class="edudash-input {{ $errors->has('email') ? 'border-error bg-error-soft/20' : '' }}" />
                    @error('email')
                        <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-normal text-secondary mb-1.5">
                        Kata sandi <span class="text-error">*</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           x-ref="passwordInput"
                           value="password"
                           placeholder="Kata sandi akun"
                           required
                           class="edudash-input {{ $errors->has('password') ? 'border-error bg-error-soft/20' : '' }}" />
                    @error('password')
                        <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 text-xs text-secondary cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-line text-primary focus:ring-primary/20" checked />
                        <span>Ingat sesi masuk</span>
                    </label>
                    <span class="text-xs text-muted">Sandi bawaan: <code class="edudash-keycap">password</code></span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="edudash-btn-primary w-full">
                        Masuk ke Dashboard
                    </button>
                </div>
            </form>

            <!-- Quick Role Switcher for Demo / Testing -->
            <div class="mt-6 pt-5 border-t border-line">
                <p class="text-xs text-muted mb-2.5">
                    Akun pengujian peran (klik untuk mengisi kredensial)
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <button type="button"
                            @click="fillDemo('admin@edudash.sch.id')"
                            class="h-8 px-3 rounded-lg bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150 text-left flex items-center justify-between">
                        <span>Admin</span>
                        <span class="font-mono text-[11px] text-muted">CRUD</span>
                    </button>
                    <button type="button"
                            @click="fillDemo('budi.santoso@sch.id')"
                            class="h-8 px-3 rounded-lg bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150 text-left flex items-center justify-between">
                        <span>Teacher</span>
                        <span class="font-mono text-[11px] text-muted">Absensi</span>
                    </button>
                    <button type="button"
                            @click="fillDemo('alya.putri@sch.id')"
                            class="h-8 px-3 rounded-lg bg-sidebar border border-line text-ink text-xs font-medium hover:bg-raised transition-colors duration-150 text-left flex items-center justify-between">
                        <span>Student</span>
                        <span class="font-mono text-[11px] text-muted">Pribadi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
