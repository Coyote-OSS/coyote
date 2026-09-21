<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

class RotationSeed {
    private int $value = 0;

    public function current(): int {
        return $this->value;
    }

    public function increment(): void {
        $this->value++;
    }
}
