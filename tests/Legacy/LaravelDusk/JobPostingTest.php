<?php

namespace Tests\Legacy\LaravelDusk;

use Coyote\Currency;
use Coyote\Firm;
use Coyote\User;
use Faker\Factory;
use Laravel\Dusk\Browser;

class JobPostingTest extends DuskTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create(['gdpr' => '{}']);
    }

    public function testSubmitWithoutFirm()
    {
        $fake = Factory::create();

        $this->browse(function (Browser $browser) use ($fake) {
            $browser
                ->loginAs($this->user)
                ->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->type('salary_from', $salaryFrom = $fake->numberBetween(0, 999))
                ->type('salary_to', $salaryTo = $fake->numberBetween(1000, 2000))
                ->select('currency_id', Currency::CHF)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->press('Zapisz')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.salary', $salaryFrom)
                ->assertSeeIn('.salary', '₣')
                ->logout();
        });
    }

    public function testSubmitWithFirm()
    {
        $fake = Factory::create();

        $this->browse(function (Browser $browser) use ($fake) {
            $browser->loginAs($this->user);

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->assertSee('Benefity')
                ->type('firm[name]', $firm = $fake->name)
                ->type('firm[website]', $website = 'http://4programmers.net')
                ->select('firm[employees]', 2)
                ->type('address', 'Wrocław Rynek')
                ->keys('input[name="address"]', '{enter}')
                ->pause(1000)
                ->type('firm[youtube_url]', 'https://www.youtube.com/watch?v=fz2OUoJpR7k')
                ->scrollTo('.footer-top')
                ->press('Zapisz')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.employer', $firm)
                ->assertSee($website);

            $this->assertDatabaseHas('firms', ['name' => $firm, 'city' => 'Wrocław']);
            $this->assertDatabaseHas('firms', ['name' => $firm, 'youtube_url' => 'https://www.youtube.com/embed/fz2OUoJpR7k']);

            $browser->logout();
        });
    }

    public function testSubmitOfferWithSecondFirm()
    {
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $this->browse(function (Browser $browser) use ($firm) {
            $browser->loginAs($this->user);

            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->assertInputValue('firm[name]', $firm->name)
                ->scrollTo('#js-submit-form')
                ->clickAtXPath('/html/body/div[1]/div[2]/div/main/div/div[4]/div[2]/div[2]/div/div/a')
                ->assertInputValue('firm[name]', '')
                ->type('firm[website]', $website = 'http://4programmers.net')
                ->type('firm[name]', 'New firm')
                ->press('Zapisz jako New firm')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.employer', 'New firm')
                ->assertSee($website);

            $browser->logout();
        });
    }

    public function testSubmitPrivateJobOfferDespiteHavingFirm()
    {
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $this->browse(function (Browser $browser) use ($firm) {
            $browser->loginAs($this->user);

            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->scrollTo('#js-submit-form')
                ->clickAtXPath('/html/body/div[1]/div[2]/div/main/div/div[4]/div[2]/div[2]/div/div/a')
                ->assertInputValue('firm[name]', '')
                ->press('Zapisz')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertDontSee($firm->name);

            $browser->logout();
        });
    }

    public function testSubmitInvalidForm()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user);
            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->assertInputValue('email', $this->user->email)
                ->press('Informacje o firmie')
                ->press('Zapisz')
                ->waitForText('Tytuł jest wymagany.')
                ->press('Oferta pracy')
                ->type('title', $fake->title)
                ->press('Informacje o firmie')
                ->press('Zapisz');

            $browser->logout();
        });
    }

    public function testSubmitWithFirmPresent()
    {
        /** @var Firm $firm */
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $this->assertNotEmpty($firm->name);

        $firm->benefits()->create(['name' => 'Game-boy']);
        $firm->benefits()->create(['name' => 'TV']);

        $this->browse(function (Browser $browser) use ($firm) {
            $browser->loginAs($this->user);
            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->title)
                ->press("Zapisz jako $firm->name")
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title);
        });
    }
}
