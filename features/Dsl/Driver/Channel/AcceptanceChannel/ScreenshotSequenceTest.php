<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScreenshotSequenceTest extends TestCase {
    private ScreenshotSequence $sequence;

    #[Before]
    public function initialize(): void {
        $this->sequence = new ScreenshotSequence(
            '/shots',
            new \DateTimeImmutable('2024-03-05 14:30:00'));
        $this->sequence->startScenario('Campaigns', 'Variant is resolved');
    }

    #[Test]
    public function buildsAPathFromTheBasePathRunDateFeatureAndScenario(): void {
        $this->assertSame(
            '/shots/2024-03-05T14-30-00/Campaigns/Variant is resolved/0001-createCampaign.png',
            $this->sequence->nextPath('createCampaign'),
        );
    }

    #[Test]
    public function theSequenceNumberIsZeroPaddedAndIncrementsWithEachCall(): void {
        $this->assertStringEndsWith('/0001-first.png', $this->sequence->nextPath('first'));
        $this->assertStringEndsWith('/0002-second.png', $this->sequence->nextPath('second'));
        $this->skipPaths(7);
        $this->assertStringEndsWith('/0010-tenth.png', $this->sequence->nextPath('tenth'));
    }

    private function skipPaths(int $count): void {
        for ($i = 0; $i < $count; $i++) {
            $this->sequence->nextPath('skipped');
        }
    }

    #[Test]
    public function sanitizesCharactersInvalidInLinuxPaths(): void {
        $this->assertStringEndsWith('/0001-abc.png', $this->sequence->nextPath("a/b\0c"));
    }

    #[Test]
    public function sanitizesCharactersInvalidInWindowsPaths(): void {
        $label = "a<b>c:d\"e/f\\g|h?i*j\x01k\x1Fl";
        $this->assertStringEndsWith('/0001-abcdefghijkl.png', $this->sequence->nextPath($label));
    }

    #[Test]
    public function sanitizesCharactersInvalidInMacPaths(): void {
        $this->assertStringEndsWith('/0001-abc.png', $this->sequence->nextPath('a:b/c'));
    }

    #[Test]
    public function neverEndsTheSanitizedNameWithASpaceOrADot(): void {
        $this->assertStringEndsWith('/0001-trailing.png', $this->sequence->nextPath('trailing '));
        $this->assertStringEndsWith('/0002-trailing.png', $this->sequence->nextPath('trailing.'));
        $this->assertStringEndsWith('/0003-trailing.png', $this->sequence->nextPath('trailing. '));
        $this->assertStringEndsWith('/0004-trailing.png', $this->sequence->nextPath('trailing .'));
    }

    #[Test]
    public function nextPathThrowsWhenStartScenarioWasNotCalled(): void {
        // given
        $sequence = new ScreenshotSequence('/', new \DateTimeImmutable());
        // then
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('startScenario() must be called before nextPath().');
        // when
        $sequence->nextPath('createCampaign');
    }

    #[Test]
    public function allowsApostrophesInFeatureAndScenarioNames(): void {
        $this->sequence->startScenario("Campaign's variant", "Editor's choice");

        $this->assertStringEndsWith(
            "/Campaign's variant/Editor's choice/0001-step.png",
            $this->sequence->nextPath('step'));
    }

    #[Test]
    public function allowsCommasInFeatureAndScenarioNames(): void {
        $this->sequence->startScenario('Campaigns, ads', 'Without a variant, slot is empty');

        $this->assertStringEndsWith(
            '/Campaigns, ads/Without a variant, slot is empty/0001-step.png',
            $this->sequence->nextPath('step'));
    }

    #[Test]
    public function allowsHyphensInFeatureAndScenarioNames(): void {
        $this->sequence->startScenario(
            'Default variant-to-slot mapping', 
            'Rectangle-xl variant');

        $this->assertStringEndsWith(
            '/Default variant-to-slot mapping/Rectangle-xl variant/0001-step.png',
            $this->sequence->nextPath('step'));
    }
}
