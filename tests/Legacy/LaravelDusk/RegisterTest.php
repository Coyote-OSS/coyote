<?php
namespace Tests\Legacy\LaravelDusk;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

class RegisterTest extends DuskTestCase
{
    private Driver $driver;

    #[Before]
    public function initialize(): void
    {
        $this->driver = new Driver();
    }

    #[Test]
    public function userRegisters()
    {
        $this->browse(function (Browser $browser) {
            $id = \uniqId();
            $browser->visit('/Register');
            $this->driver->closeGdprIfVisible($browser);
            $browser->type('name', "Mark Twain.$id");
            $browser->type('email', "mark.twain.$id@gmail.com");
            $browser->type('password', 'blueberries');
            $browser->type('password_confirmation', 'blueberries');
            $browser->check('terms');
            $browser->press('Utwórz konto');
            $browser->assertPathIs('/User');
            $browser->logout();
        });
    }

    #[Test]
    public function providingExistingUserName_resultsInRegisterFailure()
    {
        $user = $this->driver->seedUser(deleted:true);
        $this->browse(function (Browser $browser) use ($user) {
            $id = \uniqId();
            $browser->visit('/Register');
            $browser->type('name', $user->name);
            $browser->type('email', "other.mail.$id@gmail.com");
            $browser->type('password', 'password');
            $browser->type('password_confirmation', 'password');
            $browser->check('terms');
            $browser->press('Utwórz konto');
            $browser->assertSee('Konto o podanej nazwie użytkownika już istnieje.');
        });
    }
}
