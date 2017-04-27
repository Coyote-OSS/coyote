<?php

use Faker\Factory;

class JobPostingCest
{
    private $user;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->createUser();
        $I->amLoggedAs($this->user);

        \Coyote\Job::reguard();
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function createJobOfferAsRegularUser(FunctionalTester $I)
    {
        $I->wantTo('Crate job offer as regular user');
        $I->amOnRoute('job.home');
        $I->click('Dodaj ofertę pracy');

        $I->canSeeCurrentRouteIs('job.submit');

        $fake = Factory::create();

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->fillField('input[name=city]', $city = 'Zielona góra');
        $I->fillField('input[name=salary_from]', $salaryFrom = $fake->numberBetween(0, 999));
        $I->fillField('input[name=salary_to]', $salaryTo = $fake->numberBetween(1000, 2000));
        $I->selectOption('currency_id', 5);

        $I->fillField('textarea[name=description]', $fake->text);
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->cantSee('Zapisz i zakończ');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->selectOption('input[name=is_private]', '1');
        $I->click('Podgląd');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($city);
        $I->see($salaryFrom, '.salary');
        $I->see('₣', '.salary');
    }

    public function createJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer as a firm');
        $I->amOnRoute('job.submit');

        $fake = Factory::create();

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->cantSee('Zapisz jako');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->seeOptionIsSelected('input[name=is_private]', '0');

        $I->fillField(['name' => 'name'], $firm = $fake->name);
        $I->fillField(['name' => 'website'], $website = 'http://4programmers.net');
        $I->fillField(['name' => 'headline'], $headline = $fake->text(20));
        $I->fillField('textarea[name=description]', $fake->text());
        $I->selectOption('select[name=employees]', 2);
        $I->fillField('country', 'Polska');
        $I->fillField('city', 'Wrocław');

        $I->click('Podgląd');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
        $I->see($website, '#box-job-firm');
        $I->see($headline, 'blockquote');

        $I->canSeeRecord('firms', ['name' => $firm, 'country_id' => 14, 'city' => 'Wrocław']);
    }

    public function createSecondJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer when firm exists');

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');
        $I->canSee("Zapisz jako $firm", '.btn-save');
        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->fillField('input[name=done]', 1);

        $I->click("Zapisz jako $firm", '.btn-save');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
    }

    public function createJobOfferWithSecondFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer and new firm');

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->canSeeInField('input[name=name]', $firm);
        $I->fillField('input[name=id]', '');
        $I->fillField(['name' => 'website'], $website = 'http://4programmers.net');
        $I->fillField(['name' => 'headline'], $headline = $fake->text(20));
        $I->fillField('input[name=name]', 'New firm');

        $I->click('Podgląd');
        $I->see($title, '.media-heading');
        $I->see('New firm', '.employer');
        $I->see($headline, 'blockquote');

        $I->click('Opublikuj');
        $I->seeCurrentRouteIs('job.offer');

        $I->see($title, '.media-heading');
        $I->see('New firm', '.employer');
        $I->see($website, '#box-job-firm');
        $I->see($headline, 'blockquote');
    }

    public function createPrivateJobOfferDespiteHavingFirm(FunctionalTester $I)
    {
        $I->wantTo('Create a private job offer despite having a firm');

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->seeOptionIsSelected('input[name=is_private]', '0');
        $I->selectOption('input[name=is_private]', '1');
        $I->fillField('input[name=done]', 1);

        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->cantSee($firm);
    }

    public function tryToCreateJobOfferWithErrors(FunctionalTester $I)
    {
        $I->wantTo('Create job offer with empty fields');
        $fake = Factory::create();

        $I->amOnRoute('job.submit');

        $I->seeOptionIsSelected('country_id', 'Polska');

        $I->fillField('title', $title = $fake->text(50));
        $I->fillField('deadline', 100);
        $I->selectOption('employment_id', 2);
        $I->selectOption('country_id', 2);
        $I->selectOption('rate_id', 2);
        $I->selectOption('remote_range', 60);

        $I->fillField('email', '');
        $I->click('Informacje o firmie');

        $I->canSeeFormHasErrors();

        $I->canSeeOptionIsSelected('country_id', 'Belgia');
        $I->canSeeOptionIsSelected('employment_id', 'Umowa zlecenie');
        $I->canSeeOptionIsSelected('rate_id', 'rocznie');
        $I->canSeeOptionIsSelected('remote_range', '60%');
        $I->seeInField('title', $title);
        $I->seeInField('email', '');
        $I->seeInField('deadline', 100);

        $I->fillField('email', $email = $fake->email);
        $I->click('Informacje o firmie');
        $I->click('Podstawowe informacje');

        $I->canSeeOptionIsSelected('country_id', 'Belgia');
        $I->canSeeOptionIsSelected('employment_id', 'Umowa zlecenie');
        $I->canSeeOptionIsSelected('rate_id', 'rocznie');
        $I->canSeeOptionIsSelected('remote_range', '60%');
        $I->seeInField('title', $title);
        $I->seeInField('email', $email);
    }

    public function tryToCreateJobOfferWithEmptyFirmName(FunctionalTester $I)
    {
        $I->wantTo('Create job offer with empty firm name');
        $fake = Factory::create();

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->click('Podgląd');
        $I->canSeeFormHasErrors();
        $I->canSeeFormErrorMessage('name', 'Nazwa firmy jest wymagana.');
        $I->fillField('name', $firm = $fake->company);

        $I->click('Podgląd');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');

        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
    }

    public function createOfferByClickingSaveAsButton(FunctionalTester $I)
    {
        $I->wantTo('Create a offer by clicking "save as" button (quick save)');

        $fake = Factory::create();
        $id = $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->haveRecord('firm_benefits', ['firm_id' => $id, 'name' => 'Game-boy']);
        $I->haveRecord('firm_benefits', ['firm_id' => $id, 'name' => 'TV']);

        $I->amOnRoute('job.submit');

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);
        $I->fillField('done', 1);

        $I->click("Zapisz jako $firm");

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');

        $I->see('Game-boy');
        $I->see('TV');
    }

    public function createPremiumOfferWithoutInvoice(FunctionalTester $I)
    {
        $I->wantTo('Create premium offer without invoice');
        $fake = Factory::create();

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->checkOption('#plan_id');
        $I->click('Informacje o firmie');
        $I->click('Podstawowe informacje');

        $I->canSeeCheckboxIsChecked('#plan_id');

        $I->click('Informacje o firmie');
        $I->selectOption('is_private', '1');
        $I->fillField('done', 1);
        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.payment');
        $I->see('Płatność poprzez bezpieczne połączenie');
        $I->seeOptionIsSelected('invoice[country_id]', '--');
        $I->uncheckOption('enable_invoice');

        $I->fillField('name', 'Jan Kowalski');
        $I->fillField('number', '5555555555554444');
        $I->fillField('cvc', '123');
        $I->fillField('payment_method_nonce', 'fake-valid-nonce');

        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę zaczniemy promowanie ogłoszenia.');

        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'boost' => 1]);
        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
        $invoice = $I->grabRecord(\Coyote\Invoice::class, ['id' => $payment->invoice_id]);

        $I->assertEquals(\Coyote\Payment::PAID, $payment->status_id);
        $I->assertNotEmpty($payment->invoice);
        $I->assertEquals(30, $payment->days);

        $I->assertEquals(null, $invoice->country_id);

        $item = $I->grabRecord(\Coyote\Invoice\Item::class, ['invoice_id' => $invoice->id]);
        $I->assertEquals(270, $item->price);
        $I->assertEquals(1.23, $item->vat_rate);
    }

    public function createPremiumOfferWithInvoice(FunctionalTester $I)
    {
        $I->wantTo('Create premium offer with invoice');
        $fake = Factory::create();

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->checkOption('#plan_id');
        $I->click('Informacje o firmie');
        $I->click('Podstawowe informacje');

        $I->canSeeCheckboxIsChecked('#plan_id');

        $I->click('Informacje o firmie');
        $I->selectOption('is_private', '1');
        $I->fillField('done', 1);
        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.payment');

        $I->fillField('name', 'Jan Kowalski');
        $I->fillField('number', '5555555555554444');
        $I->fillField('cvc', '123');
        $I->fillField('payment_method_nonce', 'fake-valid-nonce');

        $country = $I->grabRecord(\Coyote\Country::class, ['code' => 'GB']);

        $I->selectOption('invoice[country_id]', $country->id);
        $I->fillField('invoice[vat_id]', '1234567');
        $I->fillField('invoice[name]', $fake->name);
        $I->fillField('invoice[city]', $fake->city);
        $I->fillField('invoice[address]', $fake->address);
        $I->fillField('invoice[postal_code]', $fake->postcode);

        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę zaczniemy promowanie ogłoszenia.');

        /** @var \Coyote\Job $job */
        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'boost' => 1]);
        /** @var \Coyote\Payment $payment */
        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
        /** @var \Coyote\Invoice $invoice */
        $invoice = $I->grabRecord(\Coyote\Invoice::class, ['id' => $payment->invoice_id]);

        $I->assertEquals(\Coyote\Payment::PAID, $payment->status_id);
        $I->assertNotEmpty($payment->invoice);
        $I->assertEquals(30, $payment->days);

        /** @var \Coyote\Invoice\Item $item */
        $item = $I->grabRecord(\Coyote\Invoice\Item::class, ['invoice_id' => $invoice->id]);
        $I->assertEquals(270, $item->price);
        $I->assertEquals(1, $item->vat_rate);
    }

    public function validatePaymentForm(FunctionalTester $I)
    {
        $I->wantTo('Validate payment form');
        $fake = Factory::create();

        $plan = $I->grabRecord(\Coyote\Plan::class);

        \Coyote\Job::unguard();

        $job = $I->haveRecord(\Coyote\Job::class, [
            'title' => $fake->text(50),
            'user_id' => $this->user->id,
            'description' => $fake->text,
            'deadline_at' => \Carbon\Carbon::now()->addDays(5)
        ]);

        $payment = $I->haveRecord(
            \Coyote\Payment::class,
            ['job_id' => $job->id, 'plan_id' => $plan->id, 'status_id' => \Coyote\Payment::NEW, 'days' => 5]
        );

        $I->amOnRoute('job.payment', [$payment->id]);
        $I->click('Zapłać i zapisz');

        $I->seeFormErrorMessage('name');
        $I->seeFormErrorMessage('number');
        $I->seeFormErrorMessage('cvc');
        $I->seeFormErrorMessage('invoice.address');
        $I->seeFormErrorMessage('invoice.postal_code');
        $I->seeFormErrorMessage('invoice.city');

        $I->fillField('name', $fake->firstName . ' ' . $fake->lastName);
        $I->fillField('number', '1111111111111111');
        $I->fillField('cvc', '012');

        $I->uncheckOption('enable_invoice');

        $I->click('Zapłać i zapisz');

        $I->seeFormErrorMessage('number', 'Wprowadzony numer karty jest nieprawidłowy.');
        $I->seeFormErrorMessage('cvc', 'Wprowadzony kod CVC jest nieprawidłowy.');

        $I->fillField('number', '4111111111111111');
        $I->fillField('payment_method_nonce', 'fake-valid-nonce');
        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę zaczniemy promowanie ogłoszenia.');
    }
}
