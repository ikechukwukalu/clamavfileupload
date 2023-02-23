<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
use Tests\TestCase;

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
}
