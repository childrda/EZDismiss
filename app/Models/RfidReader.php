<?php

namespace App\Models;

use App\Models\Concerns\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfidReader extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasSchoolScope;

    protected $fillable = [
        'school_id',
        'name',
        'lane',
        'endpoint_type',
        'ip_address',
        'api_key',
        'enabled',
        'last_seen_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'lane' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}

