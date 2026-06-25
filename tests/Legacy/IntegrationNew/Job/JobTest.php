<?php
namespace Tests\Legacy\IntegrationNew\Job;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

class JobTest extends TestCase {
    use Server\Http;

    #[Test]
    public function salaryArray() {
        $this->laravel
            ->get('/Praca?salary[]=1')
            ->assertSuccessful();
    }

    #[Test]
    public function salaryNonInteger() {
        $this->laravel
            ->get('/Praca?salary=foo')
            ->assertSuccessful();
    }
}
