# EDUDASH — School Management System

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

## Quick Start

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
