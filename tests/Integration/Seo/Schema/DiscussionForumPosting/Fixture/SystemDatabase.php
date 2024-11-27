<?php
namespace Tests\Integration\Seo\Schema\DiscussionForumPosting\Fixture;

use Illuminate\Support\Facades\DB;
use Tests\Integration\BaseFixture\Server\Laravel;

trait SystemDatabase
{
    use Laravel\Transactional;

    function systemDatabaseTimezone(string $timezone): void
    {
        DB::statement("SET TIMEZONE TO '$timezone'");
    }
}
