<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SchoolClass extends Model
{
    use Archivable, HasFactory, LogsActivity;

    protected $table = 'tbl_classes';

    protected $fillable = [
        'code',
        'slug',
        'name',
        'description',
        'capacity',
        'archived',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('classes')
            ->logOnly(['code', 'name', 'capacity', 'archived'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::saving(function (self $schoolClass) {
            if (empty($schoolClass->slug) || $schoolClass->isDirty('name') || $schoolClass->isDirty('code')) {
                $schoolClass->slug = Str::slug($schoolClass->code.'-'.$schoolClass->name);
            }
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'tbl_class_teacher', 'class_id', 'teacher_id')
            ->withTimestamps();
    }
}
