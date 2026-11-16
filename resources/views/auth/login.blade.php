<!DOCTYPE html>
<html lang="id" data-theme="edudash">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — EDUDASH School Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-page text-ink min-h-screen flex items-center justify-center p-6">

    <div class="max-w-[960px] w-full grid grid-cols-1 lg:grid-cols-12 bg-surface rounded-[24px] shadow-card border border-line overflow-hidden">
        <!-- Left Promo / Branding Column -->
        <div class="lg:col-span-5 bg-promo p-8 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <span class="size-10 rounded-full bg-ink text-white font-bold text-sm flex items-center justify-center">ED</span>
                    <span class="text-xl font-bold tracking-tight text-ink">EDUDASH</span>
                </div>

                <div class="mt-6 bg-white/80 rounded-2xl p-3">
                    <img src="{{ asset('img/welcome-illustration.svg') }}" alt="Ilustrasi Sistem Manajemen Sekolah EDUDASH" class="w-full h-auto rounded-xl" />
                </div>

                <h2 class="text-xl font-bold text-ink mt-6 leading-snug">
                    Sistem Manajemen Sekolah Terpusat & Efisien
                </h2>
                <p class="text-xs text-ink/80 mt-2 leading-relaxed">
                    Kelola data siswa, guru, kelas, dan mata pelajaran dalam satu antarmuka modern yang ringan dan responsif.
                </p>
            </div>

            <div class="mt-8 pt-4 border-t border-ink/10 flex items-center justify-between text-xs text-ink/75">
                <span>SMA Contoh Nusantara</span>
                <span class="font-semibold">TA 2026/2027</span>
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
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-soft text-primary text-[11px] font-semibold">
                    ● Portal Akademik Terpadu
                </span>
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-ink mt-3">
                    Selamat datang kembali.
                </h1>
                <p class="text-xs text-muted mt-1">
                    Masuk menggunakan akun <code class="text-ink font-medium">tbl_users</code> (Admin, Teacher, atau Student).
                </p>
            </div>

            @if (session('toast'))
                <div class="mt-4 px-4 py-2.5 rounded-full bg-success-soft text-[#15803D] text-xs font-semibold">
                    {{ session('toast') }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-ink mb-1.5">
                        Email atau Username <span class="text-error font-bold">*</span>
                    </label>
                    <input type="text"
                           id="email"
                           name="email"
                           x-ref="emailInput"
                           value="{{ old('email', 'admin@edudash.sch.id') }}"
                           placeholder="Masukkan email atau username..."
                           autofocus
                           required
                           class="edudash-input {{ $errors->has('email') ? 'border-error bg-error-soft/30 text-error' : '' }}" />
                    @error('email')
                        <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-ink mb-1.5">
                        Kata Sandi <span class="text-error font-bold">*</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           x-ref="passwordInput"
                           value="password"
                           placeholder="Masukkan kata sandi..."
                           required
                           class="edudash-input {{ $errors->has('password') ? 'border-error bg-error-soft/30 text-error' : '' }}" />
                    @error('password')
                        <p class="text-[11px] font-medium text-error mt-1.5 pl-3">⚠ {{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 text-xs text-muted cursor-pointer">
                        <input type="checkbox" name="remember" class="checkbox checkbox-xs checkbox-primary rounded-md" checked />
                        <span>Ingat sesi masuk saya</span>
                    </label>
                    <span class="text-[11px] text-muted">Password default: <code class="text-ink font-semibold">password</code></span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="edudash-btn-primary w-full py-3 text-sm">
                        Masuk ke Dashboard EDUDASH
                    </button>
                </div>
            </form>

            <!-- Quick Role Switcher for Demo / Testing -->
            <div class="mt-7 pt-5 border-t border-line">
                <p class="text-[11px] font-semibold text-muted uppercase tracking-wider mb-2.5">
                    Pilih Cepat Akun Pengujian Role (Klik untuk isi otomatis):
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <button type="button"
                            @click="fillDemo('admin@edudash.sch.id')"
                            class="px-3 py-2 rounded-full bg-primary-soft text-primary text-xs font-semibold hover:bg-primary hover:text-white transition text-center">
                        Admin (Full CRUD)
                    </button>
                    <button type="button"
                            @click="fillDemo('budi.santoso@sch.id')"
                            class="px-3 py-2 rounded-full bg-success-soft text-[#15803D] text-xs font-semibold hover:bg-success hover:text-white transition text-center">
                        Teacher (Read-Only)
                    </button>
                    <button type="button"
                            @click="fillDemo('alya.putri@sch.id')"
                            class="px-3 py-2 rounded-full bg-warning-soft text-[#B45309] text-xs font-semibold hover:bg-warning hover:text-white transition text-center">
                        Student (Read-Only)
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
