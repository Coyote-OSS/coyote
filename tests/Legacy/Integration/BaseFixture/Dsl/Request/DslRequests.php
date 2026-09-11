<?php
namespace Tests\Legacy\Integration\BaseFixture\Dsl\Request;

class DslRequests
{
    private int $lastReferenceNumber = 1;

    public function createTopic(?string $discussMode): CreateTopic
    {
        return $this->topic('Newbie', $discussMode);
    }

    private function topic(string $categorySlug, ?string $discussMode): CreateTopic
    {
        return new CreateTopic(
            $categorySlug,
            'Title title title ' . $this->lastReferenceNumber++,
            $discussMode);
    }
}
