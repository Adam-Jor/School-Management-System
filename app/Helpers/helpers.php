<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

if (! function_exists('indonesian_month_short')) {
    /**
     * Singkatan nama bulan dalam Bahasa Indonesia.
     */
    function indonesian_month_short(int $month): string
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return $months[$month] ?? 'Jan';
    }
}

if (! function_exists('format_number')) {
    /**
     * Format angka dengan pemisah ribuan titik (contoh: 10.000 / 1.042).
     */
    function format_number(int|float|null $number, int $decimals = 0): string
    {
        if ($number === null) {
            return '0';
        }

        return number_format((float) $number, $decimals, ',', '.');
    }
}

if (! function_exists('format_date')) {
    /**
     * Format tanggal Indonesia singkat sesuai PRD & design.md (contoh: 1 Feb 2024).
     */
    function format_date(string|DateTimeInterface|null $date, bool $withTime = false): string
    {
        if (empty($date)) {
            return '–';
        }

        $carbon = $date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);
        $formatted = $carbon->day.' '.indonesian_month_short($carbon->month).' '.$carbon->year;

        if ($withTime) {
            $formatted .= ' · '.$carbon->format('H:i');
        }

        return $formatted;
    }
}

if (! function_exists('edudash_dashboard_stats')) {
    /**
     * Ambil ringkasan metrik KPI dan delta bulanan dari database secara terpusat (cached).
     *
     * @return array<string, int>
     */
    function edudash_dashboard_stats(): array
    {
        return Cache::remember('edudash_dashboard_stats', 180, function () {
            $thisMonthStart = now()->startOfMonth();
            $lastMonthStart = now()->startOfMonth()->subMonth();
            $lastMonthEnd = $thisMonthStart->copy()->subSecond();

            return [
                'users' => User::count(),
                'users_this_month' => User::where('created_at', '>=', $thisMonthStart)->count(),
                'users_last_month' => User::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
                'students' => Student::count(),
                'students_active' => Student::where('status', 'Aktif')->count(),
                'students_this_month' => Student::where('created_at', '>=', $thisMonthStart)->count(),
                'students_last_month' => Student::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
                'teachers' => Teacher::count(),
                'teachers_active' => Teacher::where('status', 'Aktif')->count(),
                'teachers_this_month' => Teacher::where('created_at', '>=', $thisMonthStart)->count(),
                'classes' => SchoolClass::count(),
                'classes_filled' => SchoolClass::has('students')->count(),
                'subjects' => Subject::count(),
                'subjects_with_teachers' => Subject::has('teachers')->count(),
            ];
        });
    }
}

if (! function_exists('upload_image')) {
    /**
     * Upload & kompres gambar otomatis ke format WebP sesuai PRD 4.2 & 7.2:
     * Lokasi: public/uploads/{folder}/YmdHis-nama_file.webp
     */
    function upload_image(UploadedFile $file, string $folder = 'students', int $maxWidth = 600, int $quality = 82): string
    {
        $directory = public_path("uploads/{$folder}");
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName, '_') ?: 'photo';
        $filename = now()->format('YmdHis').'-'.$safeName.'.webp';
        $fullPath = $directory.DIRECTORY_SEPARATOR.$filename;

        try {
            if (class_exists(ImageManager::class)) {
                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());
                $image->scaleDown(width: $maxWidth, height: $maxWidth);
                $image->toWebp($quality)->save($fullPath);

                return "uploads/{$folder}/{$filename}";
            }
        } catch (Throwable $e) {
            // Fallback ke GD native jika diperlukan
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'webp');
        $fallbackName = now()->format('YmdHis').'-'.$safeName.'.'.$ext;
        $file->move($directory, $fallbackName);

        return "uploads/{$folder}/{$fallbackName}";
    }
}
