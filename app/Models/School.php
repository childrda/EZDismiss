<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'lane_count',
        'lane_color_mode',
        'default_lane_behavior',
        'timezone',
        'pickup_start_time',
        'pickup_end_time',
        'logo_path',
        'primary_color',
        'api_key',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function homerooms(): HasMany
    {
        return $this->hasMany(Homeroom::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function rfidReaders(): HasMany
    {
        return $this->hasMany(RfidReader::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}

