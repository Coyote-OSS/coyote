<?php
namespace Tests\Legacy\Integration\BaseFixture\Server;

use PHPUnit\Framework\Attributes\Before;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel\StaticLaravel;

trait Http {
    use Laravel\Application;

    var ?Server $server = null;

    #[Before]
    function initializeServer(): void {
        $this->server = new Server(StaticLaravel::get($this));
    }
}
