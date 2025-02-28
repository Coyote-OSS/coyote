<?php
namespace Tests\Legacy\IntegrationNew\Breadcrumbs\Fixture;

use Tests\Legacy\IntegrationNew\BaseFixture\Neon\Selector\Selector;
use Tests\Legacy\IntegrationNew\BaseFixture\View;
use Tests\Legacy\IntegrationNew\BaseFixture\View\ViewDom;

trait BreadcrumbView
{
    use View\HtmlView;

    function breadcrumbs(string $uri): array
    {
        $breadcrumbs = [];
        $dom = new ViewDom($this->htmlView($uri));
        /** @var \DOMElement $breadcrumb */
        foreach ($dom->elements(xPath:"/html/body/footer//ul/li[@class='breadcrumb-item neon-breadcrumb']/*") as $breadcrumb) {
            $name = \trim($breadcrumb->textContent);
            if (empty($name)) {
                continue;
            }
            $breadcrumbs[] = $this->breadcrumb($breadcrumb);
        }
        return $breadcrumbs;
    }

    function breadcrumb(\DOMElement $breadcrumb): Breadcrumb
    {
        $isAnchor = $breadcrumb->tagName === 'a';
        return new Breadcrumb(
            \trim($breadcrumb->textContent),
            $isAnchor,
            $this->hrefAttribute($breadcrumb));
    }

    function hrefAttribute(\DOMElement $breadcrumb): ?string
    {
        if ($breadcrumb->hasAttribute('href')) {
            return $breadcrumb->getAttribute('href');
        }
        return null;
    }

    function breadcrumbsContainerVisible(string $uri): bool
    {
        $dom = new ViewDom($this->htmlView($uri));
        $selector = new Selector('div', 'div', 'ul.breadcrumb');
        /** @var \DOMElement $breadcrumb */
        foreach ($dom->elements($selector->xPath()) as $container) {
            return true;
        }
        return false;
    }
}
