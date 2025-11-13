<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class CallUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Call $call)
    {
        $this->call->loadMissing('student', 'checkin.driver');
    }

    public function broadcastOn(): Channel
    {
        return new Channel("school.{$this->call->school_id}");
    }

    public function broadcastAs(): string
    {
        return 'CallUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'call' => $this->call->toArray(),
        ];
    }
}

