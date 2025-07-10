<?php
namespace Coyote\Domain;

class Clock {
    public function year(): int {
        return date('Y');
    }

    public function executionTime(): string {
        $time = \round($this->executionTimeAsSeconds() * 1000);
        return "$time ms";
    }

    public function executionTimeAsSeconds(): float {
        if (defined('LARAVEL_START')) {
            return \microtime(true) - \LARAVEL_START;
        }
        return 0;
    }
}
