<?php
namespace Coyote\Services\Assets;

use Coyote\Models\Asset;
use Illuminate\Http\UploadedFile;

class UploadedFileStorage {
    public function storeAndReturnUrl(UploadedFile $file, string $directory): string {
        return (string)Url::make($this->store($file, $directory));
    }

    #[\Deprecated]
    public function storeAndReturnAssetAndUrl(UploadedFile $file, string $directory): array {
        $asset = $this->store($file, $directory);
        return [$asset, (string)Url::make($asset)];
    }

    private function store(UploadedFile $file, string $directory): Asset {
        return $this->createAsset($file, $file->store($directory));
    }

    private function createAsset(UploadedFile $file, string $path): Asset {
        return Asset::query()->create([
            'name' => $this->resolveName($file, $path, 'screenshot'),
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);
    }

    private function resolveName(UploadedFile $file, string $path, string $defaultName): string {
        if ($file->getClientOriginalName() === 'blob') {
            $date = \date('YmdHis');
            $extension = $this->extension($path);
            return "$defaultName-$date.$extension";
        }
        return $file->getClientOriginalName();
    }

    private function extension(string $path): string {
        return \strToLower(\pathInfo($path, \PATHINFO_EXTENSION));
    }
}
