<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_classes';

    protected $fillable = [
        'code',
        'slug',
        'name',
        'description',
        'capacity',
        'archived',
    ];

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
}
