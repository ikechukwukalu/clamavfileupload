<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ikechukwukalu\Clamavfileupload\Facade\FileUpload;

class ClamavQueuedFileScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FileUpload $fileUpload;
    public array $tmpFiles;
    public array $settings;

    /**
     * Create a new event instance.
     */
    public function __construct(string $fileUpload, array $tmpFiles, array $settings)
    {
        $this->fileUpload = new $fileUpload();
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
