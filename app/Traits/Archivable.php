<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait Archivable
{
    public static function bootArchivable(): void
    {
        static::addGlobalScope('not_archived', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable().'.archived', 0);
        });

        static::saved(function () {
            Cache::forget('edudash_dashboard_stats');
        });

        static::deleted(function () {
            Cache::forget('edudash_dashboard_stats');
        });
    }

    public function initializeArchivable(): void
    {
        if (! isset($this->casts['archived'])) {
            $this->casts['archived'] = 'integer';
        }
    }

    public function scopeWithArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_archived');
    }

    public function scopeOnlyArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_archived')
            ->where($this->getTable().'.archived', 1);
    }

    public function archive(): bool
    {
        $this->archived = 1;
        $saved = $this->save();
        Cache::forget('edudash_dashboard_stats');

        return $saved;
    }

    public function restoreFromArchive(): bool
    {
        $this->archived = 0;
        $saved = $this->save();
        Cache::forget('edudash_dashboard_stats');

        return $saved;
    }

    public function isArchived(): bool
    {
        return (int) $this->archived === 1;
    }
}
