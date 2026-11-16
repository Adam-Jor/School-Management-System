<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subject extends Model
{
    use Archivable, HasFactory, LogsActivity;

    protected $table = 'tbl_subjects';

    protected $fillable = [
        'code',
        'slug',
        'name',
        'description',
        'credit_hours',
        'archived',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('subjects')
            ->logOnly(['code', 'name', 'credit_hours', 'archived'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::saving(function (self $subject) {
            if (empty($subject->slug) || $subject->isDirty('name') || $subject->isDirty('code')) {
                $subject->slug = Str::slug($subject->code.'-'.$subject->name);
            }
        });
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'subject_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'subject_id');
    }
}
