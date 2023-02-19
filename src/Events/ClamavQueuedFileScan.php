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
    public string $ref;

    /**
     * Create a new event instance.
     */
    public function __construct(array $tmpFiles, array $settings, string $ref)
    {
        $this->tmpFiles = $tmpFiles;
        $this->settings = $settings;
        $this->ref = $ref;
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
