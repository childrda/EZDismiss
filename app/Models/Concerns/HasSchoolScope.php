<?php

namespace App\Models\Concerns;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait HasSchoolScope
{
    public static function bootHasSchoolScope(): void
    {
        static::creating(function ($model): void {
            if (!$model->getAttribute('school_id') && Tenant::schoolId()) {
                $model->setAttribute('school_id', Tenant::schoolId());
            }
        });

        static::addGlobalScope('school', function (Builder $builder): void {
            if (!Tenant::isDistrictAdmin() && Tenant::schoolId()) {
                $builder->where($builder->getModel()->getTable() . '.school_id', Tenant::schoolId());
            }
        });
    }

    public function scopeForSchool(Builder $builder, int $schoolId): Builder
    {
        return $builder->where($builder->getModel()->getTable() . '.school_id', $schoolId);
    }
}

