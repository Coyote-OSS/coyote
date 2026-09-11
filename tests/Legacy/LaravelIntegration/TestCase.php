<?php
namespace Tests\Legacy\LaravelIntegration;

use Coyote\Http\Resources\TopicResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    /**
     * @before
     * @deprecated
     */
    public function resetLaravelStatic(): void
    {
        TopicResource::wrap('data');
    }
}
