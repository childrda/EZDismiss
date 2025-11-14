<?php

namespace App\Services;

use App\Events\CheckinCreated;
use App\Events\QueueUpdated;
use App\Models\Checkin;
use App\Models\Driver;
use App\Models\School;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function manualCheckin(School $school, Driver $driver, int $lane, ?int $insertPosition = null): Checkin
    {
        return DB::transaction(function () use ($school, $driver, $lane, $insertPosition): Checkin {
            if ($insertPosition !== null) {
                // Insert at a specific position - shift existing checkins
                $this->shiftPositions($school, $lane, $insertPosition);
                $position = $insertPosition;
            } else {
                // Append to the end
                $position = $this->nextPosition($school, $lane);
            }

            $checkin = Checkin::create([
                'school_id' => $school->id,
                'driver_id' => $driver->id,
                'method' => 'manual',
                'lane' => $lane,
                'position' => $position,
            ]);

            // Broadcast events immediately
            broadcast(new CheckinCreated($checkin));
            broadcast(new QueueUpdated($school, $lane));

            return $checkin;
        });
    }

    protected function shiftPositions(School $school, int $lane, int $insertPosition): void
    {
        // Get all checkins in this lane that need to be shifted
        $checkinsToShift = Checkin::where('school_id', $school->id)
            ->where('lane', $lane)
            ->where('position', '>=', $insertPosition)
            ->orderBy('position')
            ->get();

        // Shift each checkin's position by 1
        foreach ($checkinsToShift as $checkin) {
            $checkin->update(['position' => $checkin->position + 1]);
        }
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

