<?php

namespace App\Services;

use App\Events\CheckinCreated;
use App\Events\QueueUpdated;
use App\Models\Checkin;
use App\Models\Driver;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function manualCheckin(School $school, Driver $driver, int $lane, ?int $position = null): Checkin
    {
        return DB::transaction(function () use ($school, $driver, $lane, $position): Checkin {
            $position = $position ?? $this->nextPosition($school, $lane);

            $checkin = Checkin::create([
                'school_id' => $school->id,
                'driver_id' => $driver->id,
                'method' => 'manual',
                'lane' => $lane,
                'position' => $position,
            ]);

            CheckinCreated::dispatch($checkin);
            QueueUpdated::dispatch($school, $lane);

            return $checkin;
        });
    }

    public function nextPosition(School $school, int $lane): int
    {
        $query = Checkin::where('school_id', $school->id);

        if ($school->lane_color_mode === 'per_lane') {
            $query->where('lane', $lane);
        }

        $max = (int) $query->max('position');

        return $max + 1;
    }

    public function positionColor(int $position, string $status = 'queued'): string
    {
        if ($status === 'released') {
            return 'blue';
        }

        if (in_array($status, ['exception', 'hold'], true)) {
            return 'red';
        }

        if ($position <= 5) {
            return 'green';
        }

        if ($position <= 10) {
            return 'yellow';
        }

        return 'gray';
    }
}

