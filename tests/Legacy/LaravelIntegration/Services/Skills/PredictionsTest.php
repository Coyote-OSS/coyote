<?php

namespace Tests\Legacy\LaravelIntegration\Services\Skills;

use Coyote\Page;
use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Services\Skills\Predictions;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Http\Request;
use Tests\Legacy\LaravelIntegration\TestCase;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\HeaderBag;

class PredictionsTest extends TestCase
{
    public function testPredictionsBasedOnSkills()
    {
        $tag = factory(Tag::class)->make(['name' => 'javascript']);
        /** @var User $user */
        $user = factory(User::class)->make();

        $user->setRelation('skills', collect([$tag]));

        $requestMock = $this->mock(Request::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('user')->andReturn($user);
            $mock->headers = new HeaderBag();
        });

        $tagRepositoryMock = $this->mock(TagRepositoryInterface::class, function (MockInterface $mock) use ($tag) {
            $mock->shouldReceive('categorizedTags')->andReturn(collect([$tag]));
        });

        $pageRepository = resolve(PageRepositoryInterface::class);

        $predictions = new Predictions($requestMock, $pageRepository, $tagRepositoryMock);
        $tags = $predictions->getTags();

        $this->assertCount(1, $tags);
        $this->assertEquals($tags[0]->name, $user->skills[0]->name);
    }

    public function testPredictionsBasedOnReferer()
    {
        $tag = factory(Tag::class)->make(['name' => 'javascript']);
        /** @var User $user */
        $user = factory(User::class)->make();

        $headersMock = $this->mock(HeaderBag::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn('/Foo');
        });

        $requestMock = $this->mock(Request::class, function (MockInterface $mock) use ($headersMock, $user) {
            $mock->shouldReceive('user')->andReturn($user);
            $mock->headers = $headersMock;
        });

        $tagRepositoryMock = $this->mock(TagRepositoryInterface::class, function (MockInterface $mock) use ($tag) {
            $mock->shouldReceive('categorizedTags')->andReturn(collect([$tag]));
        });

        $pageRepositoryMock = $this->mock(PageRepositoryInterface::class, function (MockInterface $mock) use ($tag) {
            $page = new Page(['tags' => collect([$tag])]);

            $mock->shouldReceive('findByPath')->andReturn($page);
        });

        $predictions = new Predictions($requestMock, $pageRepositoryMock, $tagRepositoryMock);
        $tags = $predictions->getTags();

        $this->assertCount(1, $tags);
        $this->assertEquals($tags[0]->name, $tag->name);
    }
}
