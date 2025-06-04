<?php
namespace Coyote\Console\Commands;

use Neon2\JobBoard\Rate;

readonly class Salary {
    public function __construct(
        public ?int $rangeFrom,
        public ?int $rangeTo,
        public Rate $rate,
    ) {}
}
