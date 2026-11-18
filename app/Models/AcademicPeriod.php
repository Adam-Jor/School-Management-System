<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AcademicPeriod extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_academic_periods';

    protected $fillable = [
        'name',
        'semester',
        'slug',
        'start_date',
        'end_date',
        'is_active',
        'archived',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'archived' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (AcademicPeriod $period) {
            if (empty($period->slug)) {
                $base = Str::slug($period->name.'-'.$period->semester);
                $slug = $base;
                $counter = 1;
                while (static::withArchived()->where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$counter}";
                    $counter++;
                }
                $period->slug = $slug;
            }
        });
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'academic_period_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->semester})";
    }
}
