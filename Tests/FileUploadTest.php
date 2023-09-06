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
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
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

    public function test_delete_all_for_file_uploaded_run_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::deleteAll($fileUpload::getRef());
    }

    public function test_delete_multiple_for_file_uploaded_run_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::deleteMultiple([$response[0]->id], $fileUpload::getRef());

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::deleteMultiple([$response[0]->id]);
    }

    public function test_delete_one_for_file_uploaded_run_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::deleteOne($response[0]->id, $fileUpload::getRef());

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::deleteOne($response[0]->id);
    }

    public function test_force_delete_all_for_file_uploaded_run_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::forceDeleteAll($fileUpload::getRef());
    }

    public function test_force_delete_multiple_for_file_uploaded_run_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::forceDeleteMultiple([$response[0]->id], $fileUpload::getRef());

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::forceDeleteMultiple([$response[0]->id]);
    }

    public function test_force_delete_one_for_file_uploaded_run_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::forceDeleteOne($response[0]->id, $fileUpload::getRef());

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);

        $fileUpload::forceDeleteOne($response[0]->id);
    }

    public function test_get_deleted_files_is_true(): void
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

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $this->assertTrue($fileUpload::deleteAll($fileUpload::getRef()));
        $response = $fileUpload::getFiles();
        $this->assertTrue($response instanceof Collection);

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $response = $fileUpload::getFiles($fileUpload::getRef(), $response[0]->id);
        $this->assertTrue($response instanceof FileUploadModel);

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $response = $fileUpload::getFiles($fileUpload::getRef(), $response->pluck('id')->toArray());
        $this->assertTrue($response instanceof Collection);

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $response = $fileUpload::getFiles($fileUpload::getRef(), $response->pluck('id')->toArray());
        $this->assertTrue($response instanceof Collection);

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $this->assertTrue($fileUpload::deleteAll($fileUpload::getRef()));
        $response = $fileUpload::getFiles($fileUpload::getRef(), $response->pluck('id')->toArray(), true);
        $this->assertTrue($response instanceof Collection);

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $this->assertTrue($fileUpload::deleteAll($fileUpload::getRef()));
        $response = $fileUpload::getFiles($fileUpload::getRef(), $response[0]->id, true);
        $this->assertTrue($response instanceof FileUploadModel);

        $fileUpload = new NoClamavFileUpload;
        $response = $fileUpload::uploadFiles($request, $settings);
        $this->assertTrue($response instanceof Collection);
        $this->assertTrue($fileUpload::deleteAll($fileUpload::getRef()));
        $response = $fileUpload::getFiles(null, null, true);
        $this->assertTrue($response instanceof Collection);
    }
}
