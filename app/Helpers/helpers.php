<?php

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $carbon = $date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);
        $formatted = $carbon->day.' '.($months[$carbon->month] ?? $carbon->format('M')).' '.$carbon->year;

        if ($withTime) {
            $formatted .= ' · '.$carbon->format('H:i');
        }

        return $formatted;
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
