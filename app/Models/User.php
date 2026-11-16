<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Archivable, HasFactory, Notifiable;

    protected $table = 'tbl_users';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'username',
        'password',
        'phone',
        'img_url',
        'role',
        'status',
        'last_login_time',
        'archived',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_time' => 'datetime',
            'password' => 'hashed',
            'archived' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user) {
            if (empty($user->slug) || $user->isDirty('name')) {
                $baseSlug = Str::slug($user->name ?: $user->username ?: 'user');
                $user->slug = $baseSlug.'-'.strtolower(Str::random(4));
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials ?: 'ED';
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
