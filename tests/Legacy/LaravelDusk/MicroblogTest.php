<?php

namespace Tests\Legacy\LaravelDusk;

use Coyote\Microblog;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;

class MicroblogTest extends DuskTestCase
{
    use WithFaker;

    public function testSubmitValid()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $faker = Factory::create();
            $text = $faker->text();

            $browser
                ->resize(1600, 1200)
                ->loginAs($user)
                ->visit('/Mikroblogi')
                ->waitFor('.editor')
                ->type('.cm-content', $text)
                ->press('Zapisz')
                ->waitForText($text)
                ->assertSee($text)
                ->assertInputValueIsNot('.cm-content', $text);

            $browser->logout();
        });
    }

    public function testSubmitComment()
    {
        $microblog = factory(Microblog::class)->create();

        $this->browse(function (Browser $browser, Browser $browser2) use ($microblog) {
            $user = factory(User::class)->create();
            $text = $this->faker->text();

            $browser2->loginAs($user)->visit('/Mikroblogi/View/' . $microblog->id)->waitForText($microblog->text);

            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi/View/' . $microblog->id)
                ->type('text', $text)
                ->click('.btn-comment-submit')
                ->waitForText($text)
                ->assertSee($text)
                ->assertInputValueIsNot('text', $text);

            $browser2->waitForText($text, 30);

            $browser->logout();
        });
    }

    public function testSubmitInvalid()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi')
                ->waitFor('.editor')
                ->press('Zapisz')
                ->waitForText('Proszę wpisać treść')
                ->assertSee('Proszę wpisać treść');

            $browser->logout();
        });
    }

    public function testSponsoredOnHomepage()
    {
        $microblog = factory(Microblog::class)->create(['is_sponsored' => true]);

        try {
            $this->browse(function (Browser $browser) use ($microblog) {
                $browser
                    ->visit('/')
                    ->waitForText($microblog->text)
                    ->assertSee($microblog->text);
            });
        } finally {
            $microblog->forceDelete();
        }
    }
}
