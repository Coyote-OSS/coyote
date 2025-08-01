<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Domain\Html;
use Coyote\Domain\Initials;
use Coyote\Domain\InitialsSvg;
use Coyote\Domain\StringHtml;
use Coyote\Services\Media\Factory;
use Coyote\Services\Media\File;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Media extends AbstractExtension {
    public function getFunctions(): array {
        return [
            new TwigFunction('logo', $this->logo(...)),
            new TwigFunction('user_avatar', $this->userAvatarOrNothing(...)),
        ];
    }

    public function userAvatarOrNothing(?string $photo, ?string $name): Html {
        if ($name === null) {
            return new StringHtml('');
        }
        return $this->userAvatar($photo, $name);
    }

    public function userAvatar(?string $photo, string $name): Html {
        if ($photo === null || $photo === '') {
            $initials = new Initials()->of($name);
            return new StringHtml(new InitialsSvg($initials)->imageSvg());
        }
        $photoUrl = $this->photo($photo);
        $htmlUsername = \htmlSpecialChars($name);
        return new StringHtml('<img class="user-avatar mw-100" src="' . $photoUrl . '" alt="' . $htmlUsername . '" ' .
            'style="width:100%; height:100%; object-fit:cover; object-position:center;">');
    }

    public function logo($filename, $secure = null): string {
        return $this->getMediaUrl('logo', $filename, 'img/logo-gray.png', $secure);
    }

    private function getMediaUrl($factory, $filename, $placeholder, $secure = null): string {
        if (!$filename) {
            return cdn($placeholder, $secure);
        }
        if (is_string($filename)) {
            return $this->getMediaFactory()->make($factory, ['file_name' => $filename])->url($secure);
        }
        if ($filename instanceof File) {
            if ($filename->getFilename()) {
                return $filename->url($secure);
            }
            return cdn($placeholder, $secure);
        }
        throw new \Exception('Parameter needs to be either string or MediaInterface object.');
    }

    private function photo(string $filename): string {
        return $this->getMediaFactory()->make('photo', ['file_name' => $filename])->url();
    }

    private function getMediaFactory(): Factory {
        return app(\Coyote\Services\Media\Factory::class);
    }
}
