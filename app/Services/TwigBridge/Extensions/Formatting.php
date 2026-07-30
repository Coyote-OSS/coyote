<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Formatting extends AbstractExtension {
    public function getFilters(): array {
        return [
            new TwigFilter('number_format', $this->formatNumber(...)),
        ];
    }

    private function formatNumber(int|float $value): string {
        return number_format($value, thousands_separator:',');
    }
}
