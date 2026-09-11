<?php
namespace Tests\Legacy\Integration\BaseFixture\Server\Laravel;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

trait Application
{
    var ?Laravel\TestCase $laravel = null;

    #[Before(30)]
    function initializeLaravel(): void
    {
        $this->laravel = StaticLaravel::get($this);
    }

    #[After]
    function finalizeLaravel(): void
    {
        StaticLaravel::destroy();
    }
}
