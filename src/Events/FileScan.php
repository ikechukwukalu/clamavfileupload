<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $scanData;

    /**
     * Create a new event instance.
     *
     * @param  array  $scanData
     */
    public function __construct(array $scanData)
    {
        $this->scanData = $scanData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
