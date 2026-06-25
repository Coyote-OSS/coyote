<?php
namespace Test\Modules\Campaigns\Store;

use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

trait CampaignStoreContractTests {
    private CampaignsStore $store;

    #[Before(10)]
    public function initializeContractTestStore(): void {
        $this->store = $this->contractTestStore();
    }

    #[Test]
    #[TestDox('given no campaigns; when list; empty')]
    public function list_returnsEmpty(): void {
        Assert::assertSame([], $this->store->listCampaigns());
    }

    #[Test]
    #[TestDox('given a campaign; when list; contains')]
    public function givenCampaign_list_returnsCampaign(): void {
        $campaignId = $this->createCampaign();
        $campaigns = $this->store->listCampaigns();
        $this->assertContainsCampaignId($campaignId, $campaigns);
    }

    #[Test]
    #[TestDox('given no campaigns; when find; returns null')]
    public function find_returnsNull(): void {
        $noSuchCampaignId = 999;
        Assert::assertNull($this->store->findCampaign($noSuchCampaignId));
    }

    #[Test]
    #[TestDox('given a campaign; when find; returns')]
    public function givenCampaign_find_returnsCampaign(): void {
        $campaignId = $this->createCampaign();
        Assert::assertNotNull($this->store->findCampaign($campaignId));
    }

    #[Test]
    #[TestDox('given a campaign; when find; returns campaign id')]
    public function givenCampaign_find_returnsCampaignId(): void {
        $campaignId = $this->createCampaign();
        Assert::assertSame($campaignId, $this->store->findCampaign($campaignId)->id);
    }

    #[Test]
    #[TestDox('given no campaigns; when update; returns false')]
    public function update_returnsFalse(): void {
        $noSuchCampaignId = 999;
        Assert::assertFalse($this->updateCampaign($noSuchCampaignId));
    }

    #[Test]
    #[TestDox('given a campaign; when update; returns true')]
    public function givenCampaign_update_returnsTrue(): void {
        $campaignId = $this->createCampaign();
        Assert::assertTrue($this->updateCampaign($campaignId));
    }

    #[Test]
    #[TestDox('when create; auto-increments')]
    public function create_autoIncrements(): void {
        $firstCampaignId = $this->createCampaign();
        $secondCampaignId = $this->createCampaign();
        Assert::assertNotSame($firstCampaignId, $secondCampaignId);
    }

    #[Test]
    #[TestDox('when create; finds payload')]
    public function givenCampaign_create_findsCampaignPayload(): void {
        // when campaign is created with payload
        $campaignId = $this->store->createCampaign(new CampaignPayload(
            'name', 'redirect', '2001-01-01 00:00:00', '2002-02-02 00:00:00', 42,
        ));
        // then campaign is found with payload
        $campaignPayload = $this->store->findCampaign($campaignId)->payload;
        Assert::assertSame('name', $campaignPayload->name);
        Assert::assertSame('redirect', $campaignPayload->redirectUrl);
        Assert::assertSame('2001-01-01 00:00:00', $campaignPayload->activeSinceDate);
        Assert::assertSame('2002-02-02 00:00:00', $campaignPayload->activeUntilDate);
        Assert::assertSame(42, $campaignPayload->activeBelowViews);
    }

    #[Test]
    #[TestDox('when create; finds payload with optional fields')]
    public function givenCampaign_create_findsCampaignPayloadWithOptionalFields(): void {
        // when campaign is created with payload unset fields
        $campaignId = $this->store->createCampaign(new CampaignPayload(
            null, '', null, null, null,
        ));
        // then campaign is found with payload with unset fields
        $campaignPayload = $this->store->findCampaign($campaignId)->payload;
        Assert::assertNull($campaignPayload->name);
        Assert::assertNull($campaignPayload->activeSinceDate);
        Assert::assertNull($campaignPayload->activeUntilDate);
        Assert::assertNull($campaignPayload->activeBelowViews);
    }

    #[Test]
    #[TestDox('given a campaign; when update; updates')]
    public function givenCampaign_update_updatesCampaign(): void {
        // given campaign has been created with payload
        $campaignId = $this->store->createCampaign(new CampaignPayload(
            '', '', null, null, null,
        ));
        // when campaign is updated with payload
        $this->store->updateCampaign($campaignId, new CampaignPayload(
            'name', 'redirect', '2001-01-01 00:00:00', '2002-02-02 00:00:00', 42,
        ));
        // then campaign is found with payload
        $campaignPayload = $this->store->findCampaign($campaignId)->payload;
        Assert::assertSame('name', $campaignPayload->name);
        Assert::assertSame('redirect', $campaignPayload->redirectUrl);
        Assert::assertSame('2001-01-01 00:00:00', $campaignPayload->activeSinceDate);
        Assert::assertSame('2002-02-02 00:00:00', $campaignPayload->activeUntilDate);
        Assert::assertSame(42, $campaignPayload->activeBelowViews);
    }

    #[Test]
    #[TestDox('given no campaigns; when create-variant; returns null')]
    public function createVariant_returnsFalse(): void {
        $noSuchCampaignId = 999;
        Assert::assertNull($this->createVariant($noSuchCampaignId));
    }

    #[Test]
    #[TestDox('when create-variant; auto-increments')]
    public function createVariant_autoIncrements(): void {
        $campaignId = $this->createCampaign();
        $firstVariantId = $this->createVariant($campaignId);
        $secondVariantId = $this->createVariant($campaignId);
        Assert::assertNotSame($firstVariantId, $secondVariantId);
    }

    #[Test]
    #[TestDox('given a campaign; when create-variant; returns variant id')]
    public function givenCampaign_createVariant_returnsVariantId(): void {
        $campaignId = $this->createCampaign();
        Assert::assertNotNull($this->createVariant($campaignId));
    }

    #[Test]
    #[TestDox('given a campaign; when create-variant; find variant id')]
    public function givenCampaign_createVariant_findsVariantId(): void {
        // given a campaign
        $campaignId = $this->createCampaign();
        // when variant is created
        $variantId = $this->createVariant($campaignId);
        // then variant is found with id
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        Assert::assertSame($variantId, $variant->id);
    }

    #[Test]
    #[TestDox('given a campaign; when create-variant; lists variant id')]
    public function givenCampaign_createVariant_listsVariantId(): void {
        // given a campaign
        $campaignId = $this->createCampaign();
        // when variant is created
        $variantId = $this->createVariant($campaignId);
        // then variant is listed with id
        [$campaign] = $this->store->listCampaigns();
        [$variant] = $campaign->variants;
        Assert::assertSame($variantId, $variant->id);
    }

    #[Test]
    #[TestDox('given a campaign; when create-variant; creates variant')]
    public function givenCampaign_createVariant_createsVariant(): void {
        $campaignId = $this->createCampaign();
        $this->createVariant($campaignId);
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        Assert::assertSame(0, $variant->views);
        Assert::assertSame(0, $variant->clicks);
    }

    #[Test]
    #[TestDox('given no variants; when view-variant; ignores')]
    #[DoesNotPerformAssertions]
    public function viewVariant_ignores(): void {
        $noSuchVariantId = 999;
        $this->store->viewVariant($noSuchVariantId);
    }

    #[Test]
    #[TestDox('given a variant; when view-variant; increases')]
    public function givenVariant_viewVariant_increasesVariantViews(): void {
        // given a variant
        $campaignId = $this->createCampaign();
        $variantId = $this->createVariant($campaignId);
        // when variant is viewed
        $this->store->viewVariant($variantId);
        // then variant views have increased
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        Assert::assertSame(1, $variant->views);
    }

    #[Test]
    #[TestDox('given a variant; when click-variant; increases')]
    public function givenVariant_clickVariant_increasesVariantClicks(): void {
        // given a variant
        $campaignId = $this->createCampaign();
        $variantId = $this->createVariant($campaignId);
        // when variant is clicked
        $this->store->clickVariant($variantId);
        // then variant clicks have increased
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        Assert::assertSame(1, $variant->clicks);
    }

    #[Test]
    #[TestDox('given a variant; when view-variant; increases-by-2')]
    public function givenVariant_viewVariant_increasesVariantViewsBy2(): void {
        // given a variant
        $campaignId = $this->createCampaign();
        $variantId = $this->createVariant($campaignId);
        // when variant is viewed
        $this->store->viewVariant($variantId);
        $this->store->viewVariant($variantId);
        // then variant views have increased by 2
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        Assert::assertSame(2, $variant->views);
    }

    #[Test]
    #[TestDox('given a variant; when click-variant; increases-by-2')]
    public function givenVariant_clickVariant_increasesVariantClicksBy2(): void {
        // given a variant
        $campaignId = $this->createCampaign();
        $variantId = $this->createVariant($campaignId);
        // when variant is clicked
        $this->store->clickVariant($variantId);
        $this->store->clickVariant($variantId);
        // then variant clicks have increased by 2
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        Assert::assertSame(2, $variant->clicks);
    }

    #[Test]
    #[TestDox('when create; lists-no-variants')]
    public function create_listsCampaignWithoutVariants(): void {
        $this->createCampaign();
        [$campaign] = $this->store->listCampaigns();
        Assert::assertEmpty($campaign->variants);
    }

    #[Test]
    #[TestDox('when create; finds-no-variants')]
    public function create_findsCampaignWithoutVariants(): void {
        $campaignId = $this->createCampaign();
        $campaign = $this->store->findCampaign($campaignId);
        Assert::assertEmpty($campaign->variants);
    }

    #[Test]
    #[TestDox('given a campaign; when create-variant; finds payload')]
    public function givenCampaign_createVariant_findsVariantPayload(): void {
        // given a campaign
        $campaignId = $this->createCampaign();
        // when variant is created with payload
        $this->store->createVariant($campaignId, VariantPayload::from('horizontal', 'image-url'));
        // then variant is found with payload
        [$variant] = $this->store->findCampaign($campaignId)->variants;
        $variantPayload = $variant->payload;
        Assert::assertSame(VariantType::Horizontal, $variantPayload->type);
        Assert::assertSame('image-url', $variantPayload->imageUrl);
    }

    #[Test]
    #[TestDox('given no variants; when find-campaign-redirect-url; returns null')]
    public function givenNoVariants_findCampaignRedirectUrl_returnsNull(): void {
        $noSuchVariantId = 999;
        Assert::assertNull($this->store->findCampaignRedirectUrl($noSuchVariantId));
    }

    #[Test]
    #[TestDox('given a campaign; when create-variant; finds campaign redirect-url')]
    public function givenCampaign_createVariant_findsCampaignRedirectUrl(): void {
        // given a campaign
        $campaignId = $this->store->createCampaign($this->examplePayloadWithRedirectUrl('campaign-redirect-url'));
        // when variant is created with payload
        $variantId = $this->createVariant($campaignId);
        // then campaign is found with redirect-url
        Assert::assertSame('campaign-redirect-url', $this->store->findCampaignRedirectUrl($variantId));
    }

    abstract protected function contractTestStore(): CampaignsStore;

    private function assertContainsCampaignId(?int $campaignId, array $campaigns): void {
        Assert::assertContains($campaignId, \array_column($campaigns, 'id'));
    }

    private function createCampaign(): ?int {
        return $this->store->createCampaign($this->examplePayload());
    }

    private function updateCampaign(int $campaignId): bool {
        return $this->store->updateCampaign($campaignId, $this->examplePayload());
    }

    private function createVariant(int $campaignId): ?int {
        return $this->store->createVariant($campaignId, $this->exampleVariantPayload());
    }

    private function examplePayload(): CampaignPayload {
        return $this->examplePayloadWithRedirectUrl('example-redirect-url');
    }

    private function examplePayloadWithRedirectUrl(string $redirectUrl): CampaignPayload {
        return new CampaignPayload(
            'example-name',
            $redirectUrl,
            '2111-01-01',
            '2222-02-02',
            999_888_777);
    }

    private function exampleVariantPayload(): VariantPayload {
        return VariantPayload::from('horizontal', 'example-image-url');
    }
}
