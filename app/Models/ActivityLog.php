<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;
use Spatie\Activitylog\Models\Activity;

class ActivityLog
{
    /**
     * Record a domain or user activity into Spatie's activity_log table.
     */
    public static function record(string $action, string $module, ?string $targetLabel = null, string $badgeColor = 'primary'): ?ActivityContract
    {
        return activity($module)
            ->causedBy(Auth::user())
            ->withProperties([
                'target_label' => $targetLabel,
                'badge_color' => $badgeColor,
            ])
            ->log($action);
    }

    public static function query()
    {
        return Activity::query();
    }
}
