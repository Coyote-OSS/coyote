<?php
namespace Tests\Legacy\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

class LoginTest extends DuskTestCase {
    private Driver $driver;

    #[Before]
    public function initialize(): void {
        $this->driver = new Driver();
    }

    #[Test]
    public function userLogins_usingEmail() {
        $user = $this->driver->seedUser(password:'123');
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login');
            $this->closeGdprIfVisible($browser);
            $browser->type('name', $user->email);
            $browser->type('password', '123');
            $browser->waitForReload(fn(Browser $browser) => $browser->press('Zaloguj się'));
            $browser->assertAuthenticated();
            $browser->logout();
        });
    }

    #[Test]
    public function blockedUser_doesNotLogin() {
        $blockedUser = $this->driver->seedUser(password:'123', blocked:true);
        $this->browse(function (Browser $browser) use ($blockedUser) {
            $browser->visit('/Login');
            $this->closeGdprIfVisible($browser);
            $browser->type('name', $blockedUser->email);
            $browser->type('password', '123');
            $browser->press('Zaloguj się');
            $browser->waitForText('Konto o tym loginie zostało zablokowane.', 10);
            $browser->assertSee('Konto o tym loginie zostało zablokowane.');
        });
    }

    #[Test]
    public function providingMissingUsername_resultsInFailedLogin() {
        $user = $this->driver->seedUser(password:'123');
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login');
            $this->closeGdprIfVisible($browser);
            $browser->type('name', $user->name . '345345');
            $browser->type('password', '123');
            $browser->press('Zaloguj się');
            $browser->waitForText('Użytkownik o podanej nazwie nie istnieje.', 10);
            $browser->assertSee('Użytkownik o podanej nazwie nie istnieje.');
        });
    }

    #[Test]
    public function providingIncorrectPassword_resultsInFailedLogin() {
        $user = $this->driver->seedUser(password:'correct password');
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login');
            $this->closeGdprIfVisible($browser);
            $browser->type('name', $user->name);
            $browser->type('password', 'incorrect');
            $browser->press('Zaloguj się');
            $browser->waitForText('Podane hasło nie jest prawidłowe.', 10);
            $browser->assertSee('Podane hasło nie jest prawidłowe.');
        });
    }

    #[Test]
    public function tooManyLoginAttempts_resultsInThrottle() {
        $user = $this->driver->seedUser(password:'correct password');
        $this->browse(function (Browser $browser) use ($user) {
            $attempt = function () use ($browser, $user) {
                $browser->visit('/Login');
                $this->closeGdprIfVisible($browser);
                $browser->type('name', $user->name);
                $browser->type('password', 'incorrect');
                $browser->press('Zaloguj się');
            };
            for ($i = 0; $i < 4; $i++) {
                $attempt();
            }
            $browser->waitForText('Zbyt wiele prób logowania', 10);
            $browser->assertSee('Zbyt wiele prób logowania');
        });
    }

    private function closeGdprIfVisible(Browser $browser): void {
        $this->driver->closeGdprIfVisible($browser);
    }
}
