<?php

namespace App\Events;

use App\Models\School;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public School $school, public int $lane)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel("school.{$this->school->id}");
    }

    public function broadcastAs(): string
    {
        return 'QueueUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'lane' => $this->lane,
        ];
    }
}

