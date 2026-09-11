<?php
namespace Tests\Legacy\Integration\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\Integration\BaseFixture\Server;
use Tests\Legacy\Integration\Topic;
use Tests\Legacy\Integration\Topic\Fixture\SpaView;

class Test extends TestCase
{
    use Topic\Fixture\Models, Server\Http;

    public function test()
    {
        $topicView = $this->topicView('Orange topic');
        $this->assertThat($topicView,
            new ArrayStructure(['title' => 'Orange topic']));
    }

    private function topicView(string $topicTitle): array
    {
        $topic = $this->newTopicTitle($topicTitle);
        return $this->topicViewVariable("/Forum/{$topic->forum->slug}/$topic->id-{$topic->slug}");
    }

    private function topicViewVariable(string $uri): array
    {
        $view = new SpaView($this->server->get($uri)->assertSuccessful()->content());
        $viewVariables = $view->jsVariables();
        return $viewVariables['topic'];
    }
}
