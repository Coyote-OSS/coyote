<?php

namespace Tests\Legacy\LaravelIntegration\Resources\Elasticsearch;

use Coyote\Http\Resources\Elasticsearch\JobResource;
use Coyote\Job;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class JobResourceTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        JobResource::withoutWrapping();
    }

    public function testMapModelWithFirm()
    {
        $job = factory(Job::class)->state('firm')->create();

        $this->assertNotNull($job->firm_id);

        $result = JobResource::make($job)->toResponse(request())->getData(true);

        $this->assertNotEmpty($result['firm']);
        $this->assertArrayHasKey('id', $result);

        $this->assertEmpty($result['firm']['logo']);
        $this->assertEquals($job->firm->name, $result['firm']['name']);
    }

    public function testMapModelWithFirmLogo()
    {
        /** @var Job $job */
        $job = factory(Job::class)->state('firm')->create();

        $filename = uniqid() . '.jpg';

        $job->firm->setAttribute('logo', $filename);
        $job->firm->save();

        $this->assertNotNull($job->firm->logo->getFilename());

        $result = JobResource::make($job)->toResponse(request())->getData(true);

        $this->assertNotEmpty($result['firm']['logo']);
        $this->assertStringContainsString($filename, $result['firm']['logo']);
        $this->assertStringNotContainsString($filename, 'uploads');
    }

    public function testMapModelWithoutFirm()
    {
        $job = factory(Job::class)->create();

        $this->assertNull($job->firm_id);

        $result = JobResource::make($job)->toResponse(request())->getData(true);

        $this->assertArrayNotHasKey('firm', $result);
    }
}
