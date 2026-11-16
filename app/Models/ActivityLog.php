<?php

namespace App\Models;

use App\Traits\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use Archivable, HasFactory;

    protected $table = 'tbl_activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'target_label',
        'badge_color',
        'archived',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function record(string $action, string $module, ?string $targetLabel = null, string $badgeColor = 'primary'): self
    {
        return static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'target_label' => $targetLabel,
            'badge_color' => $badgeColor,
        ]);
    }
}
