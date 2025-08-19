<?php
namespace Coyote\Services\Stream\Objects;

use Coyote\User;

class MassDelete extends ObjectAbstract {
    public string $username;

    public function __construct(
        User          $user,
        public string $contentType,
        public int    $itemsCount,
    ) {
        $this->id = $user->id;
        $this->username = $user->name;
        $this->url = route('profile', [$this->id], false);
        $this->objectType = 'massDelete';
        parent::__construct([]);
    }
}
