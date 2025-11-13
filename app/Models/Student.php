<?php

namespace App\Models;

use App\Models\Concerns\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasSchoolScope;

    protected $fillable = [
        'school_id',
        'powerschool_id',
        'name',
        'grade',
        'homeroom_id',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function homeroom(): BelongsTo
    {
        return $this->belongsTo(Homeroom::class);
    }

    public function authorizedPickups(): HasMany
    {
        return $this->hasMany(AuthorizedPickup::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ExceptionCase::class);
    }
}

