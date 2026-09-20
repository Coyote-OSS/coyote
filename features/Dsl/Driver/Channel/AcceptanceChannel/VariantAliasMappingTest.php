<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VariantAliasMappingTest extends TestCase {
    #[Test]
    public function readsBackTheAliasSetForAnImageUrl(): void {
        $mapping = new VariantAliasMapping();
        $mapping->setVariantAlias('https://example.test/real.png', 'banner.png');
        $this->assertSame('banner.png', $mapping->getVariantAlias('https://example.test/real.png'));
    }

    #[Test]
    public function readingAnUnknownImageUrl_throws(): void {
        $mapping = new VariantAliasMapping();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Image URL has not been assigned an alias: https://example.test/unknown.png');
        $mapping->getVariantAlias('https://example.test/unknown.png');
    }

    #[Test]
    public function overridingAnAlreadySetImageUrl_throws(): void {
        $mapping = new VariantAliasMapping();
        $mapping->setVariantAlias('https://example.test/real.png', 'banner.png');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Image URL already has an alias assigned: https://example.test/real.png');
        $mapping->setVariantAlias('https://example.test/real.png', 'other.png');
    }
}
