<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Domain\Html;
use Coyote\Domain\Icon\Icons;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Icon extends AbstractExtension {
    private readonly Icons $icons;

    public function __construct() {
        $this->icons = new Icons();
    }

    public function getFunctions(): array {
        return [new TwigFunction('icon', $this->icon(...))];
    }

    private function icon(string $iconName, array $options = []): Html {
        if (\array_key_exists('spin', $options)) {
            return $this->icons->iconSpin($iconName);
        }
        if (\array_key_exists('title', $options)) {
            return $this->icons->iconTitle($iconName, $options['title']);
        }
        return $this->icons->icon($iconName);
    }
}
