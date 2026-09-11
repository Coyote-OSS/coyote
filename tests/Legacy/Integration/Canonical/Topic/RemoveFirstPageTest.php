<?php
namespace Tests\Legacy\Integration\Canonical\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Canonical;

class RemoveFirstPageTest extends TestCase {
    use Canonical\Fixture\Assertion,
        Canonical\Topic\Fixture\Models;

    /**
     * @test
     */
    public function base() {
        $uri = $this->newTopic();
        $this->assertNoRedirectGet("/Forum/{$uri}");
    }

    /**
     * @test
     */
    public function pageFirst() {
        $uri = $this->newTopic();
        $this->assertNoRedirectGet("/Forum/$uri?page=1");
    }

    /**
     * @test
     */
    public function pageSecond() {
        $uri = $this->newTopic();
        $this->assertNoRedirectGet("/Forum/{$uri}?page=2");
    }
}
