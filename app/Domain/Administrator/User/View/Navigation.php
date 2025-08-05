<?php
namespace Coyote\Domain\Administrator\User\View;

use Coyote\Domain\Administrator\View\Mention;

readonly class Navigation {
    public string $stream;
    public string $users;
    public string $settings;
    public string $profile;
    public string $posts;
    public string $comments;
    public string $microblogs;
    public string $receivedFlags;
    public Mention $mention;
    public string $inspect;

    public function __construct(int $userId, string $username) {
        $this->stream = route('adm.stream', ['actor_displayName' => $username]);
        $this->users = route('adm.users');
        $this->settings = route('adm.users.save', [$userId]);
        $this->profile = route('profile', [$userId]);
        $this->posts = route('adm.flag', ['filter' => "type:post author:$userId"]);
        $this->comments = route('adm.flag', ['filter' => "type:comment author:$userId"]);
        $this->microblogs = route('adm.flag', ['filter' => "type:microblog author:$userId"]);
        $this->receivedFlags = route('adm.flag', ['filter' => "is:reported author:$userId"]);
        $this->mention = new Mention($userId, $username);
        $this->inspect = route('adm.users.inspect', [$userId]);
    }
}
