<?php
namespace Neon\Test\Integration;

use Neon\Test\Internal\WebDriver;

readonly class DesignSystemDsl
{
    public function __construct(private WebDriver $web) {}

    public function renderHtml(string $code): void
    {
        $query = \http_build_query(['htmlMarkup' => $code]);
        $this->web->navigate('/DesignSystem?' . $query);
    }

    public function cssProperty(string $cssSelector, string $cssProperty): string
    {
        return $this->web->find($cssSelector)->cssProperty($cssProperty);
    }
}
