<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RotationSeedTest extends TestCase {
    #[Test]
    public function readsTheInitialValueAsZero(): void {
        $seed = new RotationSeed();
        $this->assertSame(0, $seed->current());
    }

    #[Test]
    public function incrementingIncreasesTheValue(): void {
        $seed = new RotationSeed();
        $seed->increment();
        $this->assertSame(1, $seed->current());
    }

    #[Test]
    public function remembersTheAccumulatedValue(): void {
        $seed = new RotationSeed();
        $seed->increment();
        $seed->increment();
        $this->assertSame(2, $seed->current());
    }
}
