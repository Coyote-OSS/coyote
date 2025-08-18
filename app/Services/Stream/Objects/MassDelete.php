<?php
namespace Coyote\Services\Stream\Objects;

use Coyote\User;

class MassDelete extends ObjectAbstract {
    public function __construct(User $user, public string $contentType) {
        $this->id = $user->id;
        $this->displayName = $user->name;
        $this->url = route('profile', [$this->id], false);
        $this->objectType = 'massDelete';
        parent::__construct([]);
    }
}
