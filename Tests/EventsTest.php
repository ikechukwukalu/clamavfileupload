<?php

namespace Ikechukwukalu\Clamavfileupload\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavIsNotRunning;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Events\FileForceDeleteFail;
use Ikechukwukalu\Clamavfileupload\Events\FileForceDeletePass;
use Ikechukwukalu\Clamavfileupload\Events\FileDeleteFail;
use Ikechukwukalu\Clamavfileupload\Events\FileDeletePass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteOne;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteOne;
use Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Listeners\FileDeleteAll;
use Ikechukwukalu\Clamavfileupload\Listeners\FileDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Listeners\FileDeleteOne;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fires_clamav_file_scan_event(): void
    {
        $event = Event::fake();
        ClamavFileScan::dispatch();
        $event->assertDispatched(ClamavFileScan::class);
    }

    public function test_fires_clamav_queued_file_scan_event(): void
    {
        $event = Event::fake();
        $file = __DIR__ . '/file/lorem-ipsum.pdf';
        if (! is_dir($tmpDir = __DIR__ . '/tmp')) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpFile = $tmpDir . '/lorem-ipsum.pdf';
        $this->assertTrue(copy($file, $tmpFile));

        ClamavQueuedFileScan::dispatch([$tmpFile], [], (string) Str::uuid());
        $event->assertDispatched(ClamavQueuedFileScan::class);
        $event->assertListening(
            ClamavQueuedFileScan::class,
            ClamavFileUpload::class
        );
    }

    public function test_fires_file_scan_events(): void
    {
        $event = Event::fake();
        FileScanFail::dispatch([]);
        $event->assertDispatched(FileScanFail::class);

        FileScanPass::dispatch([]);
        $event->assertDispatched(FileScanPass::class);
    }

    public function test_saved_files_into_db_event(): void
    {
        $event = Event::fake();
        SavedFilesIntoDB::dispatch(new FileUploadModel, (string) str::uuid());
        $event->assertDispatched(SavedFilesIntoDB::class);
    }

    public function test_clamav_is_not_running_event(): void
    {
        $event = Event::fake();
        ClamavIsNotRunning::dispatch();
        $event->assertDispatched(ClamavIsNotRunning::class);
    }

    public function test_fires_file_delete_events(): void
    {
        $event = Event::fake();
        FileDeleteFail::dispatch([]);
        $event->assertDispatched(FileDeleteFail::class);

        FileDeletePass::dispatch([]);
        $event->assertDispatched(FileDeletePass::class);
    }

    public function test_fires_file_force_delete_events(): void
    {
        $event = Event::fake();
        FileForceDeleteFail::dispatch([]);
        $event->assertDispatched(FileForceDeleteFail::class);

        FileForceDeletePass::dispatch([]);
        $event->assertDispatched(FileForceDeletePass::class);
    }

    public function test_fires_queue_file_delete_events(): void
    {
        $event = Event::fake();
        QueuedDeleteAll::dispatch('abc');
        $event->assertDispatched(QueuedDeleteAll::class);
        $event->assertListening(
            QueuedDeleteAll::class,
            FileDeleteAll::class
        );

        QueuedDeleteMultiple::dispatch('abc', []);
        $event->assertDispatched(QueuedDeleteMultiple::class);
        $event->assertListening(
            QueuedDeleteMultiple::class,
            FileDeleteMultiple::class
        );

        QueuedDeleteOne::dispatch('abc', 1);
        $event->assertDispatched(QueuedDeleteOne::class);
        $event->assertListening(
            QueuedDeleteOne::class,
            FileDeleteOne::class
        );
    }

    public function test_fires_queue_file_force_delete_events(): void
    {
        $event = Event::fake();
        QueuedForceDeleteAll::dispatch('abc');
        $event->assertDispatched(QueuedForceDeleteAll::class);
        $event->assertListening(
            QueuedForceDeleteAll::class,
            FileDeleteAll::class
        );

        QueuedForceDeleteMultiple::dispatch('abc', []);
        $event->assertDispatched(QueuedForceDeleteMultiple::class);
        $event->assertListening(
            QueuedForceDeleteMultiple::class,
            FileDeleteMultiple::class
        );

        QueuedForceDeleteOne::dispatch('abc', 1);
        $event->assertDispatched(QueuedForceDeleteOne::class);
        $event->assertListening(
            QueuedForceDeleteOne::class,
            FileDeleteOne::class
        );
    }
}
