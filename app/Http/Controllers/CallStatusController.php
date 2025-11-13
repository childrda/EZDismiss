<?php

namespace App\Http\Controllers;

use App\Events\CallUpdated;
use App\Events\QueueUpdated;
use App\Models\Call;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CallStatusController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function update(Request $request, Call $call): Response
    {
        $data = $request->validate([
            'status' => ['required', 'in:called,en_route,staged,released,hold'],
        ]);

        $oldStatus = $call->status;
        $call->update([
            'status' => $data['status'],
            'by_user_id' => $request->user()->id,
        ]);

        $call->loadMissing('checkin.school');
        CallUpdated::dispatch($call);
        QueueUpdated::dispatch($call->checkin->school, $call->checkin->lane);

        $this->logger->log('queue_status_change', 'Call status updated', [
            'call_id' => $call->id,
            'checkin_id' => $call->checkin_id,
            'student_id' => $call->student_id,
            'old_status' => $oldStatus,
            'new_status' => $data['status'],
        ]);

        return response()->noContent();
    }
}

