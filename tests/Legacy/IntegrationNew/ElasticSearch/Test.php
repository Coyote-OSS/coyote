<?php
namespace Tests\Legacy\IntegrationNew\ElasticSearch;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

class Test extends TestCase
{
    use Laravel\Application;

    /**
     * @test
     */
    public function emptyQueryParamArrayTags()
    {
        $this->laravel->get('/Praca?tags[]')->assertSuccessful();
    }
}
