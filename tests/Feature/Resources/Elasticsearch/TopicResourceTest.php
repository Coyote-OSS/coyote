<?php


namespace Tests\Feature\Resources\Elasticsearch;

use Carbon\Carbon;
use Coyote\Http\Resources\Elasticsearch\TopicResource;
use Coyote\Topic;
use Tests\TestCase;

class TopicResourceTest extends TestCase
{
    public function testMapModelIntoResourceWithOneWordSubject()
    {
        $carbon = Carbon::parse('2020-04-09 00:00');

        $topic = factory(Topic::class)->state('id')->make(['score' => 1, 'replies' => 1, 'subject' => 'Lorem', 'last_post_created_at' => $carbon]);

        TopicResource::withoutWrapping();
        $resource = TopicResource::make($topic)->toResponse(request())->getData(true);

        $this->assertEquals($topic->subject, $resource['subject']);
        $this->assertCount(1, $resource['suggest']);
        $this->assertEquals($topic->subject, $resource['suggest'][0]['input']);
        $this->assertEquals(1096, $resource['suggest'][0]['weight']);
        $this->assertEquals($topic->replies, $resource['replies']);
        $this->assertEquals($topic->score, $resource['score']);
        $this->assertEmpty($resource['subscribers']);
        $this->assertEmpty($resource['participants']);
        $this->assertNull($resource['user_id']);
    }

    public function testMapModelIntoResourceWithMultipleWordSubject()
    {
        $carbon = Carbon::parse('2020-04-09 00:00');

        $topic = factory(Topic::class)->state('id')->make(['score' => 1, 'replies' => 1, 'subject' => 'Lorem ipsum', 'last_post_created_at' => $carbon]);

        TopicResource::withoutWrapping();
        $resource = TopicResource::make($topic)->toResponse(request())->getData(true);

        $this->assertCount(2, $resource['suggest']);

        $this->assertEquals($topic->subject, $resource['suggest'][0]['input']);
        $this->assertEquals(1096, $resource['suggest'][0]['weight']);

        $this->assertEquals('ipsum', $resource['suggest'][1]['input']);
        $this->assertEquals(996, $resource['suggest'][1]['weight']);
    }
}
