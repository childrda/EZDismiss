<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Support\Tenant;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(string $eventType, ?string $description = null, array $context = []): void
    {
        ActivityLog::create([
            'school_id' => Tenant::schoolId(),
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'description' => $description,
            'context' => $context,
            'created_at' => now(),
        ]);
    }
}

