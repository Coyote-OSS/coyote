<?php
namespace Tests\Legacy\IntegrationNew\Topic\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Legacy\IntegrationNew\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newTopicTitle(string $title): Topic
    {
        return $this->storeThread(new Forum, new Topic(['title' => $title]));
    }
}
