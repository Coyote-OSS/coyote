<?php
namespace Test\Modules\Campaigns\Store;

use Modules\Campaigns\Store\CampaignsStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryCampaignsStore::class)]
class InMemoryCampaignsStoreTest extends TestCase {
    use CampaignStoreContractTests;

    protected function contractTestStore(): CampaignsStore {
        return new InMemoryCampaignsStore();
    }
}
