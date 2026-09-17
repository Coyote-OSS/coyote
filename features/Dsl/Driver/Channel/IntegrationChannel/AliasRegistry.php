<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

readonly class AliasRegistry {
    private string $suffix;

    public function __construct() {
        $this->suffix = \bin2hex(\random_bytes(8));
    }

    public function alias(string $name): string {
        return "$name-$this->suffix";
    }
}
