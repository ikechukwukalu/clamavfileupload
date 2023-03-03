<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SavedFilesIntoDB
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FileUploadModel|EloquentCollection $files;
    public string $ref;

    /**
     * Create a new event instance.
     *
     * @param  FileUploadModel|EloquentCollection $files
     * @param  string  $ref
     */
    public function __construct(FileUploadModel|EloquentCollection $files, string $ref)
    {
        $this->files = $files;
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
