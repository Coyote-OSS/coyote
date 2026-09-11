<?php
namespace Tests\Legacy\Integration\Topic;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Dsl\RunsDsl;

class TreeTopicTest extends TestCase
{
    use RunsDsl;

    #[Test]
    public function userWithAlphaAccess_canCreateTreeTopic(): void
    {
        $this->dsl->loginUser(permissionName:'alpha-access');
        $this->dsl->createTopic(discussMode:'tree');
        $this->dsl->assertTopicCreated();
        $this->dsl->assertTopicModeTree();
    }

    #[Test]
    public function userWithoutAlphaAccess_canNotCreateTreeTopic(): void
    {
        $this->dsl->loginUserNew();
        $this->dsl->createTopic(discussMode:'tree');
        $this->dsl->assertTopicNotCreated();
    }

    #[Test]
    public function guest_canNotCreateTreeTopic(): void
    {
        $this->dsl->createTopic();
        $this->dsl->assertTopicNotCreated();
    }
}
