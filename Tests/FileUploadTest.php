<?php

namespace Ikechukwukalu\Clamavfileupload\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Ikechukwukalu\Clamavfileupload\Facades\Services\FileUpload;
use Ikechukwukalu\Clamavfileupload\Facades\Services\NoClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Facades\Services\QueuedFileUpload;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_upload_run_is_true(): void
    {
        Storage::fake(config('clamavfileupload.disk'));

        $file = __DIR__ . '/file/lorem-ipsum.pdf';
        if (! is_dir($tmpDir = __DIR__ . '/tmp')) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpFile = $tmpDir . '/lorem-ipsum.pdf';
        $this->assertTrue(copy($file, $tmpFile));

        $request = new Request;
        $files = [];
        $extension = explode('.', $tmpFile)[1];
        $files[] = new UploadedFile($tmpFile, ".{$extension}");
        $input = config('clamavfileupload.input', 'file');
        $request->files->set($input, $files);

        $this->assertTrue($request instanceof Request);

        $settings = [
            'folder' => 'docs',
            'name' => 'Resumes'
        ];

        $response = FileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection || is_bool($response));
    }

    public function test_queue_file_upload_run_is_true(): void
    {
        Storage::fake(config('clamavfileupload.disk'));

        $file = __DIR__ . '/file/lorem-ipsum.pdf';
        if (! is_dir($tmpDir = __DIR__ . '/tmp')) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpFile = $tmpDir . '/lorem-ipsum.pdf';
        $this->assertTrue(copy($file, $tmpFile));

        $request = new Request;
        $files = [];
        $extension = explode('.', $tmpFile)[1];
        $files[] = new UploadedFile($tmpFile, ".{$extension}");
        $input = config('clamavfileupload.input', 'file');
        $request->files->set($input, $files);

        $this->assertTrue($request instanceof Request);

        $settings = [
            'folder' => 'docs',
            'name' => 'Resumes'
        ];

        $event = Event::fake();

        $response = QueuedFileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection || is_bool($response));

        $event->assertListening(
            ClamavQueuedFileScan::class,
            ClamavFileUpload::class
        );
    }

    public function test_no_clamav_file_upload_run_is_true(): void
    {
        Storage::fake(config('clamavfileupload.disk'));

        $file = __DIR__ . '/file/lorem-ipsum.pdf';
        if (! is_dir($tmpDir = __DIR__ . '/tmp')) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpFile = $tmpDir . '/lorem-ipsum.pdf';
        $this->assertTrue(copy($file, $tmpFile));

        $request = new Request;
        $files = [];
        $extension = explode('.', $tmpFile)[1];
        $files[] = new UploadedFile($tmpFile, ".{$extension}");
        $input = config('clamavfileupload.input', 'file');
        $request->files->set($input, $files);

        $this->assertTrue($request instanceof Request);

        $settings = [
            'folder' => 'docs',
            'name' => 'Resumes'
        ];

        $response = NoClamavFileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
    }
}
