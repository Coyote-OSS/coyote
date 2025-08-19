<?php
namespace Coyote\Services\Stream\Objects;

use Coyote\Services\Adm\UserContent\UserContentItemType;
use Coyote\User;

class MassDelete extends ObjectAbstract {
    public string $username;
    public string $itemTypeValue;

    public function __construct(
        User                $user,
        UserContentItemType $type,
        public int          $deletedItems,
    ) {
        $this->id = $user->id;
        $this->username = $user->name;
        $this->url = route('profile', [$this->id], false);
        $this->objectType = 'massDelete';
        $this->itemTypeValue = $type->value;
        parent::__construct([]);
    }
}
