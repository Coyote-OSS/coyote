<?php

use Coyote\Notification;
use Coyote\Notification\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Type::unguard();
        Type::query()->forceCreate([
            'id'       => Notification::POST_COMMENT_VOTED,
            'name'     => '...ocenie Twojego komentarza',
            'headline' => '{sender} docenił Twój komentarz',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
    }

    public function down(): void {
        Type::query()->where('id', Notification::POST_COMMENT_VOTED)->delete();
    }
};
