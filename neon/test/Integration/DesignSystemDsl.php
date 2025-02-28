<?php
namespace Neon\Test\Integration;

use Neon\Test\Internal\Color\Color;
use Neon\Test\Internal\WebDriver;

readonly class DesignSystemDsl
{
    public function __construct(private WebDriver $web) {}

    public function setTheme(string $theme): void
    {
        $this->visitDesignSystem(['theme' => $theme]);
    }

    public function showSection(string $sectionName): void
    {
        $this->visitDesignSystem(['section' => $sectionName]);
    }

    public function cssColor(string $cssSelector, string $cssProperty): string
    {
        return Color::parseRgbaAsHex($this->cssProperty($cssSelector, $cssProperty));
    }

    public function cssProperty(string $cssSelector, string $cssProperty): string
    {
        return $this->web->find($cssSelector)->cssProperty($cssProperty);
    }

    public function backgroundColor(): string
    {
        return Color::parseRgbaAsHex($this->cssProperty('body', 'background-color'));
    }

    private function visitDesignSystem(array $queryParams): void
    {
        $this->web->navigate('/DesignSystem?' . \http_build_query($queryParams));
    }

    public function setViewportWidth(int $width): void
    {
        $this->web->resize($width, 800);
    }
}
