<?php

namespace Coyote\Services\Assets;

use Coyote\Models\Asset;
use Illuminate\Contracts\Filesystem\Filesystem;

class Url
{
    private Filesystem $filesystem;
    private Asset $asset;
    private Thumbnail $thumbnail;

    public function __construct(Filesystem $filesystem, Asset $asset, Thumbnail $thumbnail)
    {
        $this->filesystem = $filesystem;
        $this->asset = $asset;
        $this->thumbnail = $thumbnail;
    }

    public static function make(Asset $asset): Url
    {
        return resolve(static::class, ['asset' => $asset]);
    }

    public function __toString()
    {
        return $this->asset->isImage() ? $this->imageUrl($this->asset->path) : $this->downloadUrl($this->asset);
    }

    public function thumbnail(\Coyote\Services\Media\Filters\Thumbnail $thumbnail): string
    {
        $thumbnailPath = $this->thumbnailPath($thumbnail);
        if (!$this->filesystem->exists($thumbnailPath)) {
            if (!$this->filesystem->exists($this->asset->path)) {
                return $this->imageUrl($this->asset->path);
            }
            $this->thumbnail->setFilter($thumbnail)->open($this->asset->path)->store($thumbnailPath);
        }
        return $this->imageUrl($thumbnailPath);
    }

    private function imageUrl(string $path): string
    {
        return $this->filesystem->url($path);
    }

    private function downloadUrl(Asset $asset): string
    {
        return route('assets.download', ['asset' => $asset->id, 'name' => basename($asset->path)]);
    }

    protected function thumbnailPath(\Coyote\Services\Media\Filters\Thumbnail $thumbnail): string
    {
        $template = strtolower(class_basename($thumbnail));
        $pathInfo = pathinfo($this->asset->path);

        return ltrim($pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template . '.' . $pathInfo['extension'], '/');
    }
}
