<?php
namespace Database\Seeders;

use Coyote\Group;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    use \SchemaBuilder;

    public function run(): void
    {
        $this->addPermission('adm-access', 'Dostęp do panelu administracyjnego');
        $this->addPermission('adm-group', 'Edycja grup i ustawień');
        $this->addPermission('adm-payment', 'Podgląd faktur i płatności');
        $this->addPermission('comment-delete', 'Usuwanie komentarzy');
        $this->addPermission('comment-update', 'Edycja komentarzy');
        $this->addPermission('firm-delete', 'Usuwanie firm');
        $this->addPermission('firm-update', 'Edycja firm');
        $this->addPermission('forum-announcement', 'Pisanie ogłoszeń');
        $this->addPermission('forum-delete', 'Kasowanie wątków i komentarzy');
        $this->addPermission('forum-emphasis', 'Operatory ! oraz !! w komentarzach na forum');
        $this->addPermission('forum-lock', 'Blokowanie wątków');
        $this->addPermission('forum-merge', 'Łączenie postów');
        $this->addPermission('forum-move', 'Przenoszenie wątków');
        $this->addPermission('forum-sticky', 'Zakładanie przyklejonych tematów');
        $this->addPermission('forum-update', 'Edycja postów i komentarzy');
        $this->addPermission('guide-delete', 'Usuwanie wpisów z Q&A');
        $this->addPermission('guide-update', 'Edycja wpisów z Q&A');
        $this->addPermission('job-delete', 'Usuwanie ofert pracy');
        $this->addPermission('job-update', 'Edycja ofert pracy');
        $this->addPermission('microblog-delete', 'Usuwanie wpisów mikrobloga');
        $this->addPermission('microblog-update', 'Edycja wpisów mikrobloga');
        $this->addPermission('pastebin-delete', 'Usuwanie wpisów z Pastebin');
        $this->addPermission('wiki-admin', 'Administracja stronami Wiki');
        $this->addPermission('alpha-access', 'Prototypowy dostęp do funkcjonalności serwisu');
        $this->addPermission('beta-access', 'Wczesny dostęp do funkcjonalności serwisu');

        /** @var Group $group */
        $group = Group::query()->where('name', 'Administrator')->first();
        $this->db->table('group_permissions')
            ->where('group_id', $group->id)
            ->update(['value' => true]);
    }

    private function addPermission(string $name, string $description): void
    {
        $this->db->table('permissions')->insert([
            'name'        => $name,
            'description' => $description,
            'default'     => false,
        ]);
    }
}
