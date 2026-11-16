<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_grades';

    protected $fillable = [
        'student_id',
        'subject_id',
        'semester',
        'score',
        'predicate',
        'archived',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'archived' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
