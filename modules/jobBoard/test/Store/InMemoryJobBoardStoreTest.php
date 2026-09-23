<?php
namespace Test\Modules\JobBoard\Store;

use Modules\JobBoard\JobBoardStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryJobBoardStore::class)]
class InMemoryJobBoardStoreTest extends TestCase {
    use JobBoardStoreContractTests;

    protected function contractTestStore(): JobBoardStore {
        return new InMemoryJobBoardStore();
    }
}
