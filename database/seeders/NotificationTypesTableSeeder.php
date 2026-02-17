<?php
namespace Database\Seeders;

use Coyote\Notification;
use Coyote\Notification\Type;
use Illuminate\Database\Seeder;

class NotificationTypesTableSeeder extends Seeder {
    public function run(): void {
        Type::unguard();

        Type::query()->forceCreate([
            'id'       => Notification::PM,
            'name'     => '...nowej wiadomości prywatnej',
            'headline' => 'Nowa wiadomość od: {sender}',
            'default'  => '["push", "mail"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::TOPIC_SUBSCRIBER,
            'name'     => '...nowych postach w obserwowanych wątkach',
            'headline' => '{sender} dodał post',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::TOPIC_MOVE,
            'name'     => '...przeniesieniu Twojego wątku',
            'headline' => 'Wątek został przeniesiony',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::TOPIC_DELETE,
            'name'     => '...usunięciu Twojego wątku',
            'headline' => 'Wątek został usunięty przez {sender}',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_DELETE,
            'name'     => '...usunięciu Twojego posta',
            'headline' => 'Post został usunięty przez {sender}',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_COMMENT,
            'name'     => '...nowym komentarzu w Twoim poście',
            'headline' => '{sender} dodał komentarz do posta',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::WIKI_SUBSCRIBER,
            'name'     => '...zmianie w obserwowanym tekście',
            'headline' => 'Modyfikacja strony',
            'category' => 'Kompendium wiedzy',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::WIKI_COMMENT,
            'name'     => '...komentarzach w obserwowanych artykułach',
            'headline' => '{sender} dodał komentarz',
            'category' => 'Kompendium wiedzy',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_EDIT,
            'name'     => '...modyfikacji Twojego posta (lub posta, który obserwujesz)',
            'headline' => '{sender} zmodyfikował post',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::TOPIC_SUBJECT,
            'name'     => '...zmianie tytułu wątku na forum',
            'headline' => 'Tytuł wątku został zmieniony',
            'category' => 'Forum',
            'default'  => '["db", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_ACCEPT,
            'name'     => 'Powiadamiaj jeżeli Twoja odpowiedź zostanie zaakceptowana przez autora wątku',
            'headline' => 'Twoja odpowiedź została zaakceptowana',
            'category' => 'Forum',
            'default'  => '["db", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_COMMENT_LOGIN,
            'name'     => 'Powiadamiaj jeżeli ktoś wspomni o Tobie w komentarzu na forum',
            'headline' => '{sender} wspomniał o Tobie w komentarzu na forum',
            'category' => 'Forum',
            'default'  => '["db", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_LOGIN,
            'name'     => '...wystąpieniu Twojego loginu w postach na forum',
            'headline' => '{sender} wspomniał o Tobie w poście na forum',
            'category' => 'Forum',
            'default'  => '["db", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::MICROBLOG_LOGIN,
            'name'     => '...wystąpieniu Twojego loginu w wiadomościach na mikroblogu',
            'headline' => '{sender} wspomniał o Tobie we wpisie mikroblogu',
            'category' => 'Mikroblogi',
            'default'  => '["db", "push", "mail"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_VOTE,
            'name'     => '...ocenie Twojego posta na forum',
            'headline' => '{sender} docenił Twój post',
            'category' => 'Forum',
            'default'  => '["db", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::MICROBLOG_VOTE,
            'name'     => '...ocenie Twojego wpisu na mikroblogu',
            'headline' => '{sender} docenił Twój wpis na mikroblogu',
            'category' => 'Mikroblogi',
            'default'  => '["db", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::MICROBLOG_SUBSCRIBER,
            'name'     => '...wpisie obserwowanego użytkownika na mikroblogu',
            'headline' => '{sender} dodał wpis na mikroblogu',
            'category' => 'Mikroblogi',
            'default'  => '["db", "push", "mail"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::MICROBLOG_COMMENT,
            'name'     => '...nowym komentarzu do obserwowanego wpisu',
            'headline' => '{sender} dodał komentarz na mikroblogu',
            'category' => 'Mikroblogi',
            'default'  => '["db", "push", "mail"]',
        ]);
        Type::query()->forceCreate([
            'id'        => Notification::FLAG,
            'name'      => '...o nowym raporcie',
            'headline'  => '{sender} dodał nowy raport',
            'is_public' => false,
            'default'   => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'        => Notification::JOB_CREATE,
            'name'      => '...o dodanej ofercie pracy',
            'headline'  => 'Dodano nową ofertę pracy',
            'is_public' => false,
            'default'   => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'        => Notification::JOB_COMMENT,
            'name'      => '...o komentarzu w obserwowanej ofercie pracy',
            'headline'  => 'Dodano komentarz do obserwowanej oferty pracy',
            'category'  => 'Praca',
            'is_public' => true,
            'default'   => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'        => Notification::JOB_APPLICATION,
            'name'      => '...o aplikacji w ofercie pracy',
            'headline'  => '{sender} wysłał aplikacje w ogłoszeniu',
            'category'  => 'Praca',
            'is_public' => false,
            'default'   => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::MICROBLOG_DELETE,
            'name'     => '...usunięciu Twojego wpisu',
            'headline' => 'Wpis został usunięty przez {sender}',
            'category' => 'Mikroblogi',
            'default'  => '["db", "mail", "push"]',
        ]);
        Type::query()->forceCreate([
            'id'       => Notification::POST_COMMENT_MIGRATED,
            'name'     => '...zamianie komentarza na post',
            'headline' => 'Komentarz został zamieniony na post przez {sender}',
            'category' => 'Forum',
            'default'  => '["db", "mail", "push"]',
        ]);
    }
}
