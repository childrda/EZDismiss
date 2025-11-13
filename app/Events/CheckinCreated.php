<?php

namespace App\Events;

use App\Models\Checkin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class CheckinCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Checkin $checkin)
    {
        $this->checkin->loadMissing('driver', 'calls.student');
    }

    public function broadcastOn(): Channel
    {
        return new Channel("school.{$this->checkin->school_id}");
    }

    public function broadcastAs(): string
    {
        return 'CheckinCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'checkin' => $this->checkin->toArray(),
        ];
    }
}

