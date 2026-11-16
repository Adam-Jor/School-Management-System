<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Teacher extends Model
{
    use Archivable, HasFactory, LogsActivity;

    protected $table = 'tbl_teachers';

    protected $fillable = [
        'user_id',
        'subject_id',
        'teacher_code',
        'slug',
        'name',
        'phone',
        'img_url',
        'status',
        'notes',
        'archived',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('teachers')
            ->logOnly(['teacher_code', 'name', 'subject_id', 'status', 'archived'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::saving(function (self $teacher) {
            if (empty($teacher->slug) || $teacher->isDirty('name') || $teacher->isDirty('teacher_code')) {
                $teacher->slug = Str::slug($teacher->teacher_code.'-'.$teacher->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function initials(): string
    {
        $cleanName = preg_replace('/[,.].*$/', '', $this->name) ?: $this->name;
        $words = preg_split('/\s+/', trim($cleanName)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials ?: 'GR';
    }
}
