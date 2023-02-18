<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClamavQueuedFileScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public array $tmpFiles;
    public array $settings;

    /**
     * Create a new event instance.
     */
    public function __construct(array $tmpFiles, array $settings)
    {
        $this->tmpFiles = $tmpFiles;
        $this->settings = $settings;
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
