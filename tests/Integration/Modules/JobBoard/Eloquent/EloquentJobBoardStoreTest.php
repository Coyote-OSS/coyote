<?php
namespace Tests\Integration\Modules\JobBoard\Eloquent;

use Coyote\Modules\JobBoard\Eloquent\EloquentJobBoardStore;
use Illuminate\Database;
use Illuminate\Database\Connection;
use Modules\JobBoard\JobBoardStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\JobBoard\Store\JobBoardStoreContractTests;
use Tests\Legacy\Integration\BaseFixture\Server;

#[CoversClass(EloquentJobBoardStore::class)]
class EloquentJobBoardStoreTest extends TestCase {
    use Server\Laravel\Transactional;
    use JobBoardStoreContractTests;

    private JobBoardStore $store;
    private Database\Connection $connection;

    #[Before(20)]
    public function initialize(): void {
        $this->connection = $this->laravel->app->get(Connection::class);
        $this->store = new EloquentJobBoardStore();
    }

    #[Test]
    public function updatesJobClicks(): void {
        $jobOfferId = $this->store->createJobOffer('job-offer-title');
        $this->store->clickJobOffer($jobOfferId);
        $this->laravel->assertSeeInDatabase('jobs', [
            'id'     => $jobOfferId,
            'clicks' => 1,
        ]);
    }

    protected function contractTestStore(): JobBoardStore {
        return $this->store;
    }
}
