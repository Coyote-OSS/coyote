<?php
namespace Coyote\Services\Adm\Cohort;

readonly class CohortRetention {
    /**
     * @param int[] $retention
     */
    public function __construct(
        public string $date,
        public array  $retention,
    ) {}

    /**
     * @return string[]
     */
    public function toFractionRetention(): array {
        return \array_map($this->formatFraction(...), $this->retention);
    }

    private function formatFraction(?int $size): ?string {
        if ($size === null) {
            return null;
        }
        $percentage = 100.0 * $size / $this->cohortSize();
        return \round($percentage, 1) . '%';
    }

    private function cohortSize(): int {
        return $this->retention[0];
    }
}
