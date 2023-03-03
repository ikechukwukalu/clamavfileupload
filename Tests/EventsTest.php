<?php

namespace Ikechukwukalu\Clamavfileupload\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavIsNotRunning;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
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
    }

    public function test_fires_clamav_file_upload_listener(): void
    {
        $event = Event::fake();
        $file = __DIR__ . '/file/lorem-ipsum.pdf';
        if (! is_dir($tmpDir = __DIR__ . '/tmp')) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpFile = $tmpDir . '/lorem-ipsum.pdf';
        $this->assertTrue(copy($file, $tmpFile));

        ClamavQueuedFileScan::dispatch([$tmpFile], [], (string) Str::uuid());

        $event->assertListening(
            ClamavQueuedFileScan::class,
            ClamavFileUpload::class
        );
    }

    public function test_fires_file_scan_fail_event(): void
    {
        $event = Event::fake();
        FileScanFail::dispatch([]);
        $event->assertDispatched(FileScanFail::class);
    }

    public function test_fires_file_scan_pass_event(): void
    {
        $event = Event::fake();
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
}
