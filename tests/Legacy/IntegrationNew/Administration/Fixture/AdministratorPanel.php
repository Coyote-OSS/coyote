<?php
namespace Tests\Legacy\IntegrationNew\Administration\Fixture;

use Coyote\Group;
use Coyote\User;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\Legacy\IntegrationNew\BaseFixture\Neon\View\ViewDom;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

trait AdministratorPanel
{
    use Server\Http;

    function userIsAdministrator(): void
    {
        $this->server->login($this->admin('admin-password'));
    }

    function administrationPanelIsOpened(): TestResponse
    {
        return $this->server->get('/Adm');
    }

    function userPassesLoginPrompt(): void
    {
        $this->server->post('/Adm', ['password' => 'admin-password']);
    }

    function assertCanAccessAdministratorDashboard(): void
    {
        $response = $this->server->get('/Adm/Dashboard');
        $this->assertPageTitle($response, 'Strona główna');
    }

    function assertLoginPromptIsPresented(TestResponse $response): void
    {
        $this->assertPageTitle($response, 'Logowanie do panelu administracyjnego');
    }

    function assertPageTitle(TestResponse $response, string $title): void
    {
        $dom = new ViewDom($response->content());
        Assert::assertStringStartsWith(
            $title,
            $dom->findString('/html/head/title/text()'));
    }

    function admin(string $password): User
    {
        $admin = $this->newUser($password);
        $this->grantAdministrator($admin);
        return $admin;
    }

    function newUser(string $password): User
    {
        $admin = new User();
        $admin->name = 'irrelevant' . \uniqId();
        $admin->email = 'irrelevant';
        $admin->password = bcrypt($password);
        $admin->save();
        return $admin;
    }

    function grantAdministrator(User $user): void
    {
        /** @var Group $group */
        $group = Group::query()->where(['name' => 'Administrator'])->firstOrFail();
        $user->groups()->attach($group->id);
    }

    function userInAdministratorDashboard(): void
    {
        $this->userIsAdministrator();
        $this->userPassesLoginPrompt();
    }

    function existingUsers(array $usernames): void
    {
        foreach ($usernames as $name) {
            if (User::query()->where('name', $name)->exists()) {
                continue;
            }
            User::query()->forceCreate(['name' => $name, 'email' => 'irrelevant']);
        }
    }
}
