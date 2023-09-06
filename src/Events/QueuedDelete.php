<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueuedDelete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array|int|string $ids;
    public null|string $ref;

    /**
     * Create a new event instance.
     *
     * @param  string  $ref
     * @param  array|int|string|null  $ids
     */
    public function __construct(array|int|string $ids, null|string $ref = null)
    {
        $this->ref = $ref;
        $this->ids = $ids;
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
