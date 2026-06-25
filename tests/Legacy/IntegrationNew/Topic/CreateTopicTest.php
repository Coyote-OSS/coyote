<?php
namespace Tests\Legacy\IntegrationNew\Topic;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Forum;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

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
