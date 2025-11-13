<?php

namespace App\Models;

use App\Models\Concerns\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasSchoolScope;

    protected $fillable = [
        'school_id',
        'name',
        'phone',
        'email',
        'vehicle_desc',
        'external_id',
        'tag_uid',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function authorizedPickups(): HasMany
    {
        return $this->hasMany(AuthorizedPickup::class);
    }
}

