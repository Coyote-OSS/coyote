<?php
namespace Tests\Legacy\Unit\Assets;

use Coyote\Models\Asset;
use Coyote\Services\Assets\Thumbnail;
use Coyote\Services\Assets\Url;
use Coyote\Services\Media\Filters\Microblog;
use Coyote\Services\Media\ImageWizard;
use Illuminate;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UrlThumbnailTest extends TestCase {
    private string $directory;
    private FilesystemAdapter $filesystem;

    #[Before]
    public function initialize(): void {
        $this->directory = $this->temporaryDirectory();
        $adapter = new Flysystem\Local\LocalFilesystemAdapter($this->directory);
        $this->filesystem = new FilesystemAdapter(
            new Flysystem\Filesystem($adapter),
            $adapter,
            ['url' => 'https://cdn.example.com']);
    }

    private function temporaryDirectory(): string {
        return \sys_get_temp_dir() . '/' . \uniqId('assets-');
    }

    #[After]
    public function removeDirectory(): void {
        new Illuminate\Filesystem\Filesystem()->deleteDirectory($this->directory);
    }

    #[Test]
    public function missingOriginalImageFallsBackToOriginalUrl(): void {
        $url = $this->url(new Asset(['name' => 'image.png', 'path' => '80804/missing.png']));
        $this->assertSame('https://cdn.example.com/80804/missing.png',
            $url->thumbnail(new Microblog()));
    }

    #[Test]
    public function existingThumbnailIsReturned(): void {
        $this->filesystem->put('80804/image-microblog.png', 'thumbnail');
        $url = $this->url(new Asset(['name' => 'image.png', 'path' => '80804/image.png']));
        $this->assertSame('https://cdn.example.com/80804/image-microblog.png',
            $url->thumbnail(new Microblog()));
    }

    private function url(Asset $asset): Url {
        $thumbnail = new Thumbnail($this->createStub(ImageWizard::class), $this->filesystem);
        return new Url($this->filesystem, $asset, $thumbnail);
    }
}
