<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Checkin;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Broadcast;

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

        broadcast(new QueueUpdated($school, $lane));

        $this->logger->log('queue_status_change', 'Checkin removed', [
            'checkin_id' => $checkin->id,
            'lane' => $lane,
        ]);

        return response()->noContent();
    }

    public function markPickedUp(Request $request, Checkin $checkin): Response
    {
        $checkin->loadMissing(['school', 'calls']);

        $lane = $checkin->lane;
        $school = $checkin->school;
        $studentsCount = $checkin->calls->count();

        // Mark all students in this checkin as released (picked up)
        $checkin->calls()->update([
            'status' => 'released',
            'by_user_id' => $request->user()->id,
        ]);

        // Log the action for each student
        foreach ($checkin->calls as $call) {
            $this->logger->log('queue_status_change', 'Student marked as picked up', [
                'call_id' => $call->id,
                'checkin_id' => $checkin->id,
                'student_id' => $call->student_id,
                'status' => 'released',
            ]);
        }

        // Remove the checkin from the queue
        $checkin->calls()->delete();
        $checkin->delete();

        broadcast(new QueueUpdated($school, $lane));

        $this->logger->log('queue_status_change', 'Checkin marked as picked up and removed', [
            'checkin_id' => $checkin->id,
            'lane' => $lane,
            'students_count' => $studentsCount,
        ]);

        return response()->noContent();
    }
}

