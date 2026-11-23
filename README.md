# School Management System

Sistem Informasi Manajemen Sekolah yang ringan dan responsif, dibangun dengan **Laravel 11**, **Livewire 3**, **Alpine.js**, **Tailwind CSS**, **daisyUI (tema `edudash`)**, dan **Heroicons**.

## Fitur Utama

- **Autentikasi & Kontrol Akses Berbasis Peran (`tbl_users`):**
  - **Admin (`admin@edudash.sch.id` / `password`):** Akses CRUD penuh di semua modul, impor/ekspor CSV, ekspor PDF, serta pengelolaan Arsip/Pemulihan data.
  - **Guru (`budi.santoso@sch.id` / `password`):** Akses baca saja ke dashboard, direktori siswa, direktori guru, kelas, dan mata pelajaran.
  - **Siswa (`alya.putri@sch.id` / `password`):** Akses baca saja dan ringkasan profil akademik pribadi.
- **Dashboard Sesuai Peran:**
  - Ringkasan KPI 5 kolom yang di-cache (`Pengguna`, `Siswa`, `Guru`, `Kelas`, `Mata Pelajaran`).
  - Grafik area pertumbuhan siswa bulanan interaktif (`Chart.js`) dengan tabel data alternatif yang aksesibel.
  - Kartu pengumuman sekolah, tabel siswa terbaru dengan badge status, dan log aktivitas real-time (`spatie/laravel-activitylog`).
- **Modul Inti (`tbl_students`, `tbl_teachers`, `tbl_classes`, `tbl_subjects`, `tbl_users`):**
  - Tabel server-side Livewire 3 (pencarian `wire:model.live.debounce.300ms`, pengurutan kolom, paginasi, skeleton loader).
  - Query relasional `JOIN` dan eager loading (`with()`) untuk mencegah query N+1.
  - Trait soft-delete `Archivable` kustom (`archived = 0/1`) dengan fungsi tempat sampah & pemulihan.
  - Impor & ekspor Excel (`.xlsx`/`.csv`) (`maatwebsite/excel`) dan laporan PDF (`barryvdh/laravel-dompdf`).
  - Kompresi dan konversi otomatis unggahan gambar ke **WebP** (`public/uploads/{folder}/YmdHis-name.webp`).
  - Format angka Indonesia (`1.042`) dan format tanggal Indonesia (`1 Feb 2024`).
  - Slug dinamis dan breadcrumb hierarkis di semua halaman.


## Instalasi

### Prasyarat

| Kebutuhan | Versi minimal |
| --- | --- |
| PHP | 8.2 (ekstensi `pdo_mysql`/`pdo_sqlite`, `mbstring`, `fileinfo`, `gd` atau `imagick`, `zip`) |
| Composer | 2.x |
| Node.js + npm | 18.x |
| Database | MySQL 8 / MariaDB 10.4, atau SQLite |

### Langkah

1. **Ambil kode proyek**

   ```bash
   git clone https://github.com/Adam-Jor/School-Management-System.git School-Management-System
   cd School-Management-System
   ```

2. **Pasang dependensi PHP**

   ```bash
   composer install
   ```

3. **Siapkan file environment**

   ```bash
   cp .env.example .env      # Windows CMD: copy .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database** (pilih salah satu)

   **MySQL/MariaDB** — buat database dulu, lalu sesuaikan `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=school_management
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   ```bash
   mysql -u root -e "CREATE DATABASE school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

   **SQLite** — tanpa server database:

   ```bash
   touch database/database.sqlite    # Windows: type nul > database\database.sqlite
   ```

   lalu di `.env`: `DB_CONNECTION=sqlite` (biarkan `DB_DATABASE` kosong).

5. **Migrasi skema + data contoh**

   ```bash
   php artisan migrate --seed
   ```

   Seeder membuat akun, kelas, mata pelajaran, siswa, guru, absensi, dan nilai contoh.

6. **Build aset frontend**

   ```bash
   npm install
   npm run build
   ```

7. **Jalankan aplikasi**

   ```bash
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000`. Login memakai akun hasil seeder di bawah.

### Akun hasil seeder

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@edudash.sch.id` | `password` |
| Guru | `budi.santoso@sch.id` | `password` |
| Siswa | `alya.putri@sch.id` | `password` |

Ganti kata sandi akun-akun ini sebelum dipakai di lingkungan nyata.

### Mode pengembangan

```bash
npm run dev      # Vite dev server (hot reload) di port 5173
php artisan serve
```

atau jalankan semuanya sekaligus: `composer run dev`.

### Pemecahan masalah

- **Halaman tampil tanpa CSS/JS, atau aset 404** — ada file `public/hot` sisa Vite dev tetapi port 5173 tidak berjalan. Jalankan `npm run dev`, atau hapus file tersebut (`del public\hot`) dan pakai hasil `npm run build`.
- **Data dashboard tidak berubah setelah seeding** — statistik di-cache: `php artisan cache:clear`.
- **Perubahan `.env` tidak terbaca** — `php artisan config:clear`.
- **Jalankan test** — `php artisan test` (Pest).

