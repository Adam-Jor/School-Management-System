<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_attendances';

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'notes',
        'archived',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'archived' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
