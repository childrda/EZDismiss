<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Checkin;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QueueCheckinController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function destroy(Request $request, Checkin $checkin): Response
    {
        $checkin->loadMissing('school');
        $lane = $checkin->lane;
        $school = $checkin->school;

        $checkin->calls()->delete();
        $checkin->delete();

        QueueUpdated::dispatch($school, $lane);

        $this->logger->log('queue_status_change', 'Checkin removed', [
            'checkin_id' => $checkin->id,
            'lane' => $lane,
        ]);

        return response()->noContent();
    }
}

