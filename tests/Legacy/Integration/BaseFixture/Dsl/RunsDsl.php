<?php
namespace Tests\Legacy\Integration\BaseFixture\Dsl;

use PHPUnit\Framework\Attributes\PreCondition;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server\Server;

trait RunsDsl
{
    use BaseFixture\Server\Laravel\Application;
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Server\Http;

    var Dsl $dsl;

    #[PreCondition]
    public function initializeDsl(): void
    {
        $this->dsl = new Dsl(
            new ModelsDriver(),
            new Server($this->laravel),
            $this->laravel,
        );
    }
}
