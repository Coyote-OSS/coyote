<?php
namespace Tests\Legacy\Integration\Seo\Assets;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;
use Tests\Legacy\Integration\Seo;

class Test extends TestCase
{
    use Laravel\Application;
    use Seo\Assets\Fixture\Fixture;

    public function test()
    {
        $id = $this->asset('file.txt');
        $this->assertThat($this->httpHeaders("/assets/$id/file.txt"), new ArrayStructure([
            'x-robots-tag' => $this->containsIdentical('noindex')
        ]));
    }

    public function httpHeaders(string $uri): array
    {
        return $this->laravel->get($uri)->headers->all();
    }
}
