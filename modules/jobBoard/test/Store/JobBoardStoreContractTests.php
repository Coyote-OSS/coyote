<?php
namespace Test\Modules\JobBoard\Store;

use Modules\JobBoard\JobBoardStore;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

trait JobBoardStoreContractTests {
    private JobBoardStore $store;

    #[Before(10)]
    public function initializeContractTestStore(): void {
        $this->store = $this->contractTestStore();
    }

    #[Test]
    #[TestDox('when create; auto-increments')]
    public function create_autoIncrements(): void {
        $firstJobOfferId = $this->createJobOffer();
        $secondJobOfferId = $this->createJobOffer();
        Assert::assertNotSame($firstJobOfferId, $secondJobOfferId);
    }

    #[Test]
    #[TestDox('given a job offer; when clicks; returns zero')]
    public function givenJobOffer_clicks_returnsZero(): void {
        $jobOfferId = $this->createJobOffer();
        Assert::assertSame(0, $this->store->jobOfferClicks($jobOfferId));
    }

    #[Test]
    #[TestDox('given a job offer; when click; increases')]
    public function givenJobOffer_click_increasesClicks(): void {
        // given a job offer
        $jobOfferId = $this->createJobOffer();
        // when job offer is clicked
        $this->store->clickJobOffer($jobOfferId);
        // then job offer clicks have increased
        Assert::assertSame(1, $this->store->jobOfferClicks($jobOfferId));
    }

    #[Test]
    #[TestDox('given a job offer; when click; increases-by-2')]
    public function givenJobOffer_click_increasesClicksBy2(): void {
        // given a job offer
        $jobOfferId = $this->createJobOffer();
        // when job offer is clicked twice
        $this->store->clickJobOffer($jobOfferId);
        $this->store->clickJobOffer($jobOfferId);
        // then job offer clicks have increased by 2
        Assert::assertSame(2, $this->store->jobOfferClicks($jobOfferId));
    }

    #[Test]
    #[TestDox('given two job offers; when click; increases only clicked')]
    public function givenTwoJobOffers_click_increasesOnlyClickedJobOffer(): void {
        // given two job offers
        $clickedJobOfferId = $this->createJobOffer();
        $otherJobOfferId = $this->createJobOffer();
        // when one job offer is clicked
        $this->store->clickJobOffer($clickedJobOfferId);
        // then the other job offer clicks have not increased
        Assert::assertSame(0, $this->store->jobOfferClicks($otherJobOfferId));
    }

    abstract protected function contractTestStore(): JobBoardStore;

    private function createJobOffer(): int {
        return $this->store->createJobOffer('example-title');
    }
}
