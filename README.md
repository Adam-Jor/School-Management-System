# School Management System

A lightweight, responsive School Management System built with **Laravel 11**, **Livewire 3**, **Alpine.js**, **Tailwind CSS**, **daisyUI (`edudash` theme)**, and **Heroicons**.

## Key Features

- **Role-Based Authentication & Access Control (`tbl_users`):**
  - **Admin (`admin@edudash.sch.id` / `password`):** Full CRUD access across all modules, CSV import/export, PDF export, and Archive/Restore management.
  - **Teacher (`budi.santoso@sch.id` / `password`):** Read-only access to dashboard, student directory, teacher directory, classes, and subjects.
  - **Student (`alya.putri@sch.id` / `password`):** Read-only access and personal academic profile overview.
- **Role-Aware Dashboard:**
  - Cached 5-column KPI summary strip (`Users`, `Students`, `Teachers`, `Classes`, `Subjects`).
  - Interactive monthly student growth area chart (`Chart.js`) with accessible data table disclosure.
  - School announcement promo card, latest students table with status badges, and real-time activity log (`spatie/laravel-activitylog`).
- **Core Modules (`tbl_students`, `tbl_teachers`, `tbl_classes`, `tbl_subjects`, `tbl_users`):**
  - Livewire 3 server-side tables (`wire:model.live.debounce.300ms` search, column sorting, pagination, skeleton loaders).
  - Relational `JOIN` queries and eager loading (`with()`) to prevent N+1 queries.
  - Custom `Archivable` soft-delete trait (`archived = 0/1`) with trash & restore functionality.
  - Excel (`.xlsx`/`.csv`) import & export (`maatwebsite/excel`) and PDF reporting (`barryvdh/laravel-dompdf`).
  - Automatic image upload compression & conversion to **WebP** (`public/uploads/{folder}/YmdHis-name.webp`).
  - Indonesian number formatting (`1.042`) and Indonesian date formatting (`1 Feb 2024`).
  - Dynamic slugs and hierarchical breadcrumbs on all pages.

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
   git clone <url-repo> School-Management-System
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

