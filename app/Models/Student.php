<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_students';

    protected $fillable = [
        'user_id',
        'class_id',
        'student_code',
        'slug',
        'name',
        'phone',
        'address',
        'img_url',
        'status',
        'archived',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $student) {
            if (empty($student->slug) || $student->isDirty('name') || $student->isDirty('student_code')) {
                $student->slug = Str::slug($student->student_code.'-'.$student->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials ?: 'SW';
    }
}
