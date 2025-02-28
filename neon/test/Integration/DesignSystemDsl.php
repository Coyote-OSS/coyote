<?php
namespace Neon\Test\Integration;

use Neon\Test\Internal\WebDriver;

readonly class DesignSystemDsl
{
    public function __construct(private WebDriver $web) {}

    public function renderHtml(string $code): void
    {
        $this->visitDesignSystem(['htmlMarkup' => $code]);
    }

    public function setTheme(string $theme): void
    {
        $this->visitDesignSystem(['theme' => $theme]);
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
}
