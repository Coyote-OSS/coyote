<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;
use Coyote\User;

class Mention extends Html {
    public function __construct(private int $userId, private string $userName) {}

    public static function from(User $user): self {
        return new self($user->id, $user->name);
    }

    protected function toHtml(): string {
        $url = route('adm.users.show', [$this->userId]);
        return '<a class="mention" href="' . \htmlSpecialChars($url) . '">' . '@' . $this->userName . '</a>';
    }
}
