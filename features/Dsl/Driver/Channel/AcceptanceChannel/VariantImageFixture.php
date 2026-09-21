<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

readonly class VariantImageFixture {
    public function create(string $variantType): string {
        $path = $this->variantTemporaryPath();
        $this->writeImage($path, ...$this->variantDimensions($variantType));
        return $path;
    }

    public function remove(string $imagePath): void {
        \unlink($imagePath);
    }

    private function variantDimensions(string $variantType): array {
        return match ($variantType) {
            'banner'         => [728, 90],
            'banner-xl'      => [728, 200],
            'leaderboard'    => [1140, 90],
            'leaderboard-xl' => [1140, 200],
            'rectangle'      => [300, 250],
            'rectangle-xl'   => [300, 600],
            default          => throw new \Exception("Invalid variant type: $variantType"),
        };
    }

    private function variantTemporaryPath(): string {
        $randomName = \bin2hex(\random_bytes(8));
        return \sys_get_temp_dir() . "/variant-$randomName.png";
    }

    private function writeImage(string $path, int $width, int $height): void {
        $image = \imageCreateTrueColor($width, $height);
        \imageFill($image, 0, 0, \imageColorAllocate($image, 255, 0, 0));
        \imagePng($image, $path);
    }
}
