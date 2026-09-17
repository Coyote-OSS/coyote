<?php
namespace Features\Behat;

use RuntimeException;

readonly class Assertion {
    public function assertContains(mixed $needle, array $haystack): void {
        if (!$this->contains($needle, $haystack)) {
            $needleFmt = \var_export($needle, true);
            $haystackFmt = \var_export($haystack, true);
            throw new \RuntimeException("Expected $haystackFmt to contain $needleFmt.");
        }
    }

    public function assertNotContains(mixed $needle, array $haystack): void {
        if ($this->contains($needle, $haystack)) {
            $needleFmt = \var_export($needle, true);
            $haystackFmt = \var_export($haystack, true);
            throw new \RuntimeException("Expected $haystackFmt to not contain $needleFmt.");
        }
    }

    public function assertEmpty(array $haystack): void {
        if (!empty($haystack)) {
            $haystackFmt = \var_export($haystack, true);
            throw new RuntimeException("Expected $haystackFmt to be empty.");
        }
    }

    private function contains(mixed $needle, array $haystack): bool {
        return \in_array($needle, $haystack, true);
    }
}
