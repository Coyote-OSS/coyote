<?php
namespace Coyote\View;

use Coyote\Domain\Administrator\View\Mention;
use Coyote\Services\Adm\UserInspection;
use Coyote\Services\Adm\UserInspection\FingerprintUserTableItem;

readonly class FingerprintUserMentionTableItem {
    /**
     * @param Mention[] $users
     */
    public function __construct(
        public string $fingerprint,
        public array  $users,
    ) {}

    public static function from(FingerprintUserTableItem $item): self {
        return new self(
            $item->fingerprint,
            \array_map(self::userMention(...), $item->users));
    }

    private static function userMention(UserInspection\User $user): Mention {
        return new Mention($user->userId, $user->username);
    }
}
