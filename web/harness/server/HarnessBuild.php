<?php
namespace Web\Harness;

readonly class HarnessBuild {
    public function __construct(private string $directory) {}

    public function file(string $path): ?string {
        $file = "$this->directory/$path";
        if (\is_file($file)) {
            return $file;
        }
        return null;
    }
}
