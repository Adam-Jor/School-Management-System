<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_schedules';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'academic_period_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'archived',
    ];

    protected $casts = [
        'archived' => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function isOngoingNow(): bool
    {
        $daysIndo = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $today = $daysIndo[now()->dayOfWeekIso] ?? '';
        if ($this->day_of_week !== $today) {
            return false;
        }

        $nowTime = now()->format('H:i:s');

        return $nowTime >= $this->start_time && $nowTime <= $this->end_time;
    }
}
