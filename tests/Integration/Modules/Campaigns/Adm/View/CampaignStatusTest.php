<?php
namespace Tests\Integration\Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\View\CampaignStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignStatus::class)]
class CampaignStatusTest extends TestCase {
    #[Test]
    public function active_hasNoStatusText(): void {
        $this->assertStatusText('active', '');
    }

    #[Test]
    public function isActive(): void {
        $this->assertTrue(new CampaignStatus('active')->active());
    }

    #[Test]
    public function isNotActive(): void {
        $this->assertFalse(new CampaignStatus('misconfigured')->active());
    }

    #[Test]
    public function misconfigured(): void {
        $this->assertStatusText('misconfigured', 'Data końca lub docelowa liczba wyświetleń nie jest określona.');
    }

    #[Test]
    public function targetReached(): void {
        $this->assertStatusText('target-reached', 'Osiągnięto docelową liczbę wyświetleń.');
    }

    #[Test]
    public function notStarted(): void {
        $this->assertStatusText('not-started', 'Kampania się nie rozpoczęła, względem daty.');
    }

    #[Test]
    public function finished(): void {
        $this->assertStatusText('finished', 'Kampania się zakończyła, względem daty.');
    }

    #[Test]
    public function failsForInvalidStatus(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid campaign status.');
        new CampaignStatus('invalid')->statusText();
    }

    private function assertStatusText(string $actualStatus, string $expectedStatusText): void {
        $this->assertSame($expectedStatusText,
            new CampaignStatus($actualStatus)->statusText());
    }
}
