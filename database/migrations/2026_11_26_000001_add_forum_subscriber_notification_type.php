<?php

use Coyote\Notification;
use Coyote\Notification\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Type::unguard();
        Type::query()->forceCreate([
            'id'       => Notification::FORUM_SUBSCRIBER,
            'name'     => '...nowym wątku w obserwowanej kategorii',
            'headline' => '{sender} utworzył nowy wątek w kategorii',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
    }

    public function down(): void {
        Type::query()->where('id', Notification::FORUM_SUBSCRIBER)->delete();
    }
};
