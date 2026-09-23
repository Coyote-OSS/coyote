<?php
namespace Features\Behat;

use RuntimeException;

readonly class Assertion {
    public function assertContains(mixed $needle, array $haystack): void {
        if (!$this->contains($needle, $haystack)) {
            $needleFmt = \json_encode($needle, true);
            $haystackFmt = \json_encode($haystack, true);
            throw new \RuntimeException("Expected $haystackFmt to contain $needleFmt.");
        }
    }

    public function assertNotContains(mixed $needle, array $haystack): void {
        if ($this->contains($needle, $haystack)) {
            $needleFmt = \json_encode($needle, true);
            $haystackFmt = \json_encode($haystack, true);
            throw new \RuntimeException("Expected $haystackFmt to not contain $needleFmt.");
        }
    }

    public function assertEmpty(array $haystack): void {
        if (!empty($haystack)) {
            $haystackFmt = \json_encode($haystack, true);
            throw new RuntimeException("Expected $haystackFmt to be empty.");
        }
    }

    public function assertEquals(mixed $expected, mixed $actual): void {
        if ($expected !== $actual) {
            $expectedFmt = \json_encode($expected, true);
            $actualFmt = \json_encode($actual, true);
            throw new RuntimeException("Expected $expectedFmt, but got $actualFmt.");
        }
    }

    private function contains(mixed $needle, array $haystack): bool {
        return \in_array($needle, $haystack, true);
    }
}
