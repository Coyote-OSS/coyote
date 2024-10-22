<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Domain\Html;
use Coyote\Domain\Initials;
use Coyote\Domain\InitialsSvg;
use Coyote\Domain\StringHtml;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Media\File;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Media extends AbstractExtension
{
    use MediaFactory;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('logo', $this->logo(...)),
            new TwigFunction('user_avatar', $this->userAvatar(...)),
        ];
    }

    public function userAvatar(?string $photo, string $name): Html
    {
        if ($photo === null || $photo === '') {
            $initials = (new Initials())->of($name);
            return new StringHtml((new InitialsSvg($initials))->imageSvg());
        }
        $photoUrl = $this->photo($photo);
        $htmlUsername = \htmlSpecialChars($name);
        return new StringHtml('<img class="user-avatar mw-100" src="' . $photoUrl . '" alt="' . $htmlUsername . '">');
    }

    public function logo($filename, $secure = null): string
    {
        return $this->getMediaUrl('logo', $filename, 'img/logo-gray.png', $secure);
    }

    private function getMediaUrl($factory, $filename, $placeholder, $secure = null): string
    {
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

    private function photo(string $filename): string
    {
        return $this->getMediaFactory()->make('photo', ['file_name' => $filename])->url();
    }
}
