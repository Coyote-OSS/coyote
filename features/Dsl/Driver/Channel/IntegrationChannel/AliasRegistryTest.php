<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AliasRegistryTest extends TestCase {
    #[Test]
    public function aliasesTheSameNameDifferentlyAcrossInstances(): void {
        $this->assertNotSame(
            new AliasRegistry()->alias('blue-offer'),
            new AliasRegistry()->alias('blue-offer'));
    }

    #[Test]
    public function aliasesTheSameNameIdenticallyWithinTheSameInstance(): void {
        $instance = new AliasRegistry();
        $this->assertSame(
            $instance->alias('blue-offer'),
            $instance->alias('blue-offer'));
    }

    #[Test]
    public function keepsDifferentNamesDistinctFromEachOther(): void {
        $registry = new AliasRegistry();
        $this->assertNotSame(
            $registry->alias('blue-offer'),
            $registry->alias('green-offer'));
    }

    #[Test]
    public function keepsTheOriginalNameIncluded(): void {
        $registry = new AliasRegistry();
        $this->assertStringContainsString('blue-offer', $registry->alias('blue-offer'));
    }
}
