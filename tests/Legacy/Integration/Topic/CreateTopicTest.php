<?php
namespace Tests\Legacy\Integration\Topic;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum;
use Tests\Legacy\Integration\BaseFixture\Server;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

class CreateTopicTest extends TestCase {
    use Laravel\Application;
    use Laravel\Transactional;
    use Server\Http;
    use Forum\Models;

    #[Test]
    public function createsTopic_asNewUser(): void {
        $this->loginAsUser();
        $response = $this->laravel->post('/Forum/Java/Submit', [
            'title' => 'One two three',
            'text'  => 'content',
        ]);
        $response->assertCreated();
        $this->laravel->assertSeeInDatabase('topics', [
            'title' => 'One two three',
        ]);
    }

    private function loginAsUser(): void {
        $this->server->loginById($this->driver->newUserReturnId(emailConfirmed:true));
    }
}
