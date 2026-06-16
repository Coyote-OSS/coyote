<?php
namespace Test\Modules\Campaigns\ProportionalInterleaverTest;

use Modules\Campaigns\ProportionalInterleaver;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProportionalInterleaver::class)]
class PropertyBasedTest extends TestCase {
    private array $samples;

    #[Before]
    public function initialize(): void {
        $this->samples = new ProportionalInterleaver()->interleave([
            ['a1', 'a2'],
            ['b1', 'b2', 'b3'],
            ['c1'],
        ]);
    }

    #[Test]
    public function sequencesAreRepresentedEqually(): void {
        $this->assertSame(\count($this->itemsOf('a')), \count($this->itemsOf('b')));
        $this->assertSame(\count($this->itemsOf('b')), \count($this->itemsOf('c')));
    }

    #[Test]
    public function everyItemIsSampled(): void {
        foreach (['a1', 'a2', 'b1', 'b2', 'b3', 'c1'] as $item) {
            $this->assertContains($item, $this->samples);
        }
    }

    #[Test]
    public function itemsWithinSequenceAreRepresentedEqually(): void {
        $this->assertSame($this->occurrences('a1'), $this->occurrences('a2'));
        $this->assertSame($this->occurrences('b1'), $this->occurrences('b2'));
        $this->assertSame($this->occurrences('b2'), $this->occurrences('b3'));
    }

    #[Test]
    public function sequencesAreSampledInOrder(): void {
        $this->assertSame('a1', $this->samples[0]);
        $this->assertSame('b1', $this->samples[1]);
        $this->assertSame('c1', $this->samples[2]);
        $this->assertSame('a2', $this->samples[3]);
        $this->assertSame('b2', $this->samples[4]);
        $this->assertSame('c1', $this->samples[5]);
    }

    #[Test]
    public function itemsAreSampledInOrder(): void {
        $a = $this->itemsOf('a');
        $this->assertSame('a1', $a[0]);
        $this->assertSame('a2', $a[1]);

        $b = $this->itemsOf('b');
        $this->assertSame('b1', $b[0]);
        $this->assertSame('b2', $b[1]);
        $this->assertSame('b3', $b[2]);

        $c = $this->itemsOf('c');
        $this->assertSame('c1', $c[0]);
    }

    private function itemsOf(string $prefix): array {
        return \array_values(\array_filter($this->samples, fn($s) => \str_starts_with($s, $prefix)));
    }

    private function occurrences(string $item): int {
        return \array_count_values($this->samples)[$item] ?? throw new \Exception();
    }
}
