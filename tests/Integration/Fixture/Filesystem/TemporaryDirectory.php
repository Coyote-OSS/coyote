<?php
namespace Tests\Integration\Fixture\Filesystem;

readonly class TemporaryDirectory {
    public string $path;

    public function __construct(string $prefix) {
        $this->path = \sys_get_temp_dir() . "/$prefix-" . \uniqId();
        \mkdir($this->path);
    }

    public function putFile(string $path, string $content): void {
        $file = "$this->path/$path";
        if (!\is_dir(\dirname($file))) {
            \mkdir(\dirname($file), recursive:true);
        }
        \file_put_contents($file, $content);
    }

    public function remove(): void {
        $this->removeDirectory($this->path);
    }

    private function removeDirectory(string $directory): void {
        foreach (\array_diff(\scanDir($directory), ['.', '..']) as $entry) {
            $path = "$directory/$entry";
            if (\is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                \unlink($path);
            }
        }
        \rmdir($directory);
    }
}
