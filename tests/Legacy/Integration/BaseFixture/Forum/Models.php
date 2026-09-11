<?php
namespace Tests\Legacy\Integration\BaseFixture\Forum;

use PHPUnit\Framework\Attributes\Before;
use Tests\Legacy\Integration\BaseFixture;

trait Models
{
    use BaseFixture\Server\Laravel\Transactional;

    var ModelsDriver $driver;

    #[Before]
    public function initializeModels(): void
    {
        $this->driver = new ModelsDriver();
    }
}
