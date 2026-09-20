<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

class VariantAliasMapping {
    /** @var array<string, string> */
    private array $aliases = [];

    public function setVariantAlias(string $imageUrl, string $alias): void {
        if (\array_key_exists($imageUrl, $this->aliases)) {
            throw new \Exception("Image URL already has an alias assigned: $imageUrl");
        }
        $this->aliases[$imageUrl] = $alias;
    }

    public function getVariantAlias(string $imageUrl): string {
        return $this->aliases[$imageUrl] ?? throw new \Exception(
            "Image URL has not been assigned an alias: $imageUrl",
        );
    }
}
