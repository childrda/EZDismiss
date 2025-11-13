<?php

namespace App\Models;

use App\Models\Concerns\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checkin extends Model
{
    use HasFactory;
    use HasSchoolScope;

    protected $fillable = [
        'school_id',
        'driver_id',
        'method',
        'lane',
        'position',
    ];

    protected $casts = [
        'lane' => 'integer',
        'position' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }
}

