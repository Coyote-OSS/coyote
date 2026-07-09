<?php
namespace Coyote\Domain\Icon;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;

readonly class Icons {
    private FontAwesomePro $fa;

    public function __construct() {
        $this->fa = new FontAwesomePro();
    }

    public function icon(string $iconName): Html {
        return $this->iconWithClass($iconName, 'fa-fw', null);
    }

    public function iconTitle(string $iconName, string $title): Html {
        return $this->iconWithClass($iconName, 'fa-fw', $title);
    }

    public function iconSpin(string $iconName): Html {
        return $this->iconWithClass($iconName, 'fa-fw fa-spin', null);
    }

    private function iconWithClass(
        string  $iconName,
        string  $modifierClass,
        ?string $title,
    ): Html {
        $class = $this->iconClass($iconName);
        return $this->iconTag("$class $modifierClass", $iconName, $title);
    }

    private function iconTag(string $class, string $iconName, ?string $title): Html {
        if ($title !== null) {
            return new StringHtml(\sPrintF('<i class="%s" data-icon="%s" title="%s"></i>', $class, $iconName, $title));
        }
        return new StringHtml(\sPrintF('<i class="%s" data-icon="%s"></i>', $class, $iconName));
    }

    private function iconClass(string $iconName): string {
        $icons = $this->fa->icons();
        if (\array_key_exists($iconName, $icons)) {
            return $icons[$iconName];
        }
        throw new \InvalidArgumentException("Failed to find icon: $iconName");
    }

    public function icons(): array {
        return $this->fa->icons();
    }
}
