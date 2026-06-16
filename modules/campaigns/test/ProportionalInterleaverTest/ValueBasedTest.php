<?php
namespace Test\Modules\Campaigns\ProportionalInterleaverTest;

use Modules\Campaigns\ProportionalInterleaver;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ValueBasedTest extends TestCase {
    private ProportionalInterleaver $interleave;

    #[Before]
    public function initialize(): void {
        $this->interleave = new ProportionalInterleaver();
    }

    #[Test]
    public function empty(): void {
        $this->assertSame([], $this->interleave->interleave([]));
    }

    #[Test]
    public function singleValue(): void {
        $value = $this->interleave->interleave([['value']]);
        $this->assertSame(['value'], $value);
    }

    #[Test]
    public function singleColumn_twoRows(): void {
        $value = $this->interleave->interleave([['value1', 'value2']]);
        $this->assertSame(['value1', 'value2'], $value);
    }

    #[Test]
    public function twoColumns_oneRow(): void {
        $interleaved = $this->interleave->interleave([['value1'], ['value2']]);
        $this->assertSame(['value1', 'value2'], $interleaved);
    }

    #[Test]
    public function twoColumns_twoRows_areInterleaved(): void {
        $interleaved = $this->interleave->interleave([
            ['a1', 'a2'],
            ['b1', 'b2'],
        ]);
        $this->assertSame(['a1', 'b1', 'a2', 'b2'], $interleaved);
    }

    #[Test]
    public function unevenMatrix_isInterleaved(): void {
        $interleaved = $this->interleave->interleave([
            ['a'],
            ['b1', 'b2', 'b3'],
        ]);
        $this->assertSame(['a', 'b1', 'a', 'b2', 'a', 'b3'], $interleaved);
    }

    #[Test]
    public function firstColumnEmpty(): void {
        $interleaved = $this->interleave->interleave([
            [],
            ['b1', 'b2'],
        ]);
        $this->assertSame(['b1', 'b2'], $interleaved);
    }

    #[Test]
    public function secondColumnEmpty(): void {
        $interleaved = $this->interleave->interleave([
            ['a1', 'a2'],
            [],
        ]);
        $this->assertSame(['a1', 'a2'], $interleaved);
    }

    #[Test]
    public function interleaveUpToLeastCommonMultiple_1x5_10(): void {
        $this->assertInterleavedCount(10, [
            ['1'], // 1, 1, 1, 1
            ['5', '5', '5', '5', '5'],
        ]);
    }

    #[Test]
    public function interleaveUpToLeastCommonMultiple_2x4_8(): void {
        $this->assertInterleavedCount(8, [
            ['2', '2'], // 2, 2
            ['4', '4', '4', '4'],
        ]);
    }

    #[Test]
    public function interleaveUpToLeastCommonMultiple_2x6_12(): void {
        $this->assertInterleavedCount(12, [
            ['2', '2'], // 2, 2, 2, 2,
            ['6', '6', '6', '6', '6', '6'],
        ]);
    }

    #[Test]
    public function interleaveUpToLeastCommonMultiple_3x6_12(): void {
        $this->assertInterleavedCount(12, [
            ['3', '3', '3'], // 3, 3, 3
            ['6', '6', '6', '6', '6', '6'],
        ]);
    }

    #[Test]
    public function interleaveUpToLeastCommonMultiple_exceptZero(): void {
        $this->assertInterleavedCount(6, [
            [],
            ['6', '6', '6', '6', '6', '6'],
        ]);
        $this->assertInterleavedCount(6, [
            ['6', '6', '6', '6', '6', '6'],
            [],
        ]);
    }

    #[Test]
    public function threeSequences(): void {
        $interleaved = $this->interleave->interleave([
            ['a1', 'a2', 'a3'],
            ['b'],
            ['c1', 'c2'],
        ]);
        $this->assertSame([
            'a1', 'b', 'c1',
            'a2', 'b', 'c2',
            'a3', 'b', 'c1',
            'a1', 'b', 'c2',
            'a2', 'b', 'c1',
            'a3', 'b', 'c2',
        ], $interleaved);
    }

    private function assertInterleavedCount(int $expectedValues, array $sequences): void {
        $this->assertCount($expectedValues, $this->interleave->interleave($sequences));
    }
}
