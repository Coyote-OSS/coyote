import {Page} from '@playwright/test';
import {JobOfferSubmitAttemptMode, Payment, PaymentMethod, PaymentSummary, PricingBundleName, PricingPlan} from './dsl';
import {WebDriver} from './webDriver';

export type PaymentNotification = string;
export type PaymentStatus = string;

export class Driver {
  private web: WebDriver;

  constructor(page: Page) {
    this.web = new WebDriver(page);
  }

  async newSession(): None {
    await this.web.openUrl('/Job');
  }

  async loadApplicationLoggedIn(userId: number): Promise<void> {
    await this.loginAndNavigate(userId, '/Job');
  }

  async tryOpenJobOfferFormWithoutSelectedPlan(): Promise<void> {
    await this.web.openUrl('/Job/new');
  }

  async tryEditJobOfferAs(jobOfferId: number, userId: number): Promise<void> {
    await this.loginAndNavigate(userId, `/Job/${jobOfferId}/edit`);
  }

  async reloadApplicationLoggedOut(): Promise<void> {
    await this.logOutAndNavigate('/Job');
  }

  async tryOpenJobOfferFormAsLoggedOut(): Promise<void> {
    await this.logOutAndNavigate('/Job/new');
  }

  async tryEditJobOfferAsLoggedOut(jobOfferId: number): Promise<void> {
    await this.logOutAndNavigate(`/Job/${jobOfferId}/edit`);
  }

  private async logOutAndNavigate(url: string): Promise<void> {
    await this.web.openUrl(url + '?workerIndex=logout');
  }

  async findJobOfferId(jobOfferTitle: string): Promise<number> {
    return parseUrlJobShowId(await this.findJobOfferUrl(jobOfferTitle));
  }

  private async loginAndNavigate(userId: number, url: string): Promise<void> {
    await this.web.openUrl(url + '?revokeBundle=true&workerIndex=' + userId);
  }

  async currentUrl(): Promise<string> {
    return this.web.currentUrlPath();
  }

  async setAcceptanceTags(tagNames: string[]): Promise<void> {
    await this.web.setAcceptanceTags(tagNames);
  }

  async publishJobOffer(
    title: string,
    pricingPlan: PricingPlan,
    payment: Payment,
    paymentCardNumber: string,
    description: string,
    companyName: string,
    expired: boolean,
  ): None {
    await this.createJobOffer(title, pricingPlan, payment, description, companyName, expired);
    if (pricingPlan === 'free') {
      await this.web.waitForText('Dodano ogłoszenie!');
    } else {
      await this.finalizeJobOfferPayment(payment, paymentCardNumber);
    }
  }

  async continuePayment(jobOfferTitle: string): Promise<void> {
    await this.web.click('Moje ogłoszenia');
    await this.web.click(jobOfferTitle);
    await this.web.click('Przejdź do płatności');
  }

  async finishPayment(paymentCardNumber: string): None {
    await this.finalizeJobOfferPayment('completed', paymentCardNumber);
  }

  private async createJobOffer(
    title: string,
    pricingPlan: PricingPlan,
    payment: Payment,
    description: string,
    companyName: string,
    expired: boolean,
  ): None {
    await this.navigateToForm(pricingPlan, payment);
    await this.submitJobOfferForm(title,
      description,
      companyName,
      pricingPlan === 'free' ? 'free' : 'paid',
      expired);
  }

  async navigateToForm(pricingPlan: PricingPlan, payment: Payment): None {
    await this.web.click('Dodaj ogłoszenie od 0zł');
    if (payment !== 'redeem-bundle') {
      await this.selectPricingPlan(pricingPlan);
    }
  }

  async initiateCardPayment(jobOfferTitle: string, cardNumber: string): None {
    await this.createJobOffer(jobOfferTitle, 'premium', 'ignored', 'description', 'companyName', false);
    await this.selectPaymentMethod('card');
    await this.fillCardDetails(cardNumber);
    await this.fillInvoiceInformation();
    await this.submitPayment();
  }

  async initiateP24Payment(jobOfferTitle: string): None {
    await this.createJobOffer(jobOfferTitle, 'premium', 'ignored', 'description', 'companyName', false);
    await this.selectPaymentMethod('p24');
    await this.fillInvoiceInformation();
    await this.submitPayment();
  }

  private async submitJobOfferForm(
    title: string,
    description: string,
    companyName: string,
    pricing: 'paid'|'free',
    expired: boolean,
  ): None {
    await this.fillJobOfferForm(title, description, companyName, expired);
    if (pricing === 'free') {
      await this.web.click('Publikuj');
    } else {
      await this.web.click('Dodaj ogłoszenie');
    }
    await this.web.waitForText('Dodano ogłoszenie!');
  }

  private async fillJobOfferForm(title: string, description: string, companyName: string, expired: boolean): None {
    await this.fillJobOfferCompanyName(companyName);
    await this.fillJobOfferDetails(title, description, expired);
  }

  async fillJobOfferCompanyName(companyName: string): None {
    await this.web.fillByLabel('Nazwa firmy', companyName);
    await this.web.click('Dalej');
  }

  private async fillJobOfferDetails(title: string, description: string, expired: boolean): None {
    await this.web.fillByLabel('Tytuł ogłoszenia', title);
    if (expired) {
      await this.web.fill('jobOfferDescription', 'acceptanceTestExpired');
    } else {
      await this.web.fill('jobOfferDescription', description);
    }
    await this.web.click('Dalej');
  }

  private async selectPricingPlan(pricingPlan: PricingPlan): None {
    if (pricingPlan === 'free') {
      await this.web.click('Publikuj ogłoszenie');
    } else if (pricingPlan === 'premium') {
      await this.web.click('Kup ogłoszenie');
    } else {
      await this.web.click('Pakiety -50%');
      if (pricingPlan === 'strategic') {
        await this.web.click('Kup pakiet Strategic');
      } else if (pricingPlan === 'growth') {
        await this.web.click('Kup pakiet Growth');
      } else if (pricingPlan === 'scale') {
        await this.web.click('Kup pakiet Scale');
      } else {
        throw new Error('Failed to select a pricing plan.');
      }
    }
  }

  private async selectPaymentMethod(paymentMethod: PaymentMethod): None {
    if (paymentMethod === 'card') {
      await this.web.click('Karta kredytowa / debetowa');
    }
    if (paymentMethod === 'p24') {
      await this.web.click('BLIK lub przelew');
    }
  }

  private async finalizeJobOfferPayment(payment: Payment, paymentCardNumber: string): None {
    await this.web.waitForText('Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność.');
    if (payment === 'completed') {
      await this.fillCardDetails(paymentCardNumber);
      await this.fillInvoiceInformation();
      await this.submitPayment();
      await this.web.waitForText('Płatność zaksięgowana!');
    }
    if (payment === 'redeem-bundle') {
      await this.finalizePaymentByUsingBundle();
      await this.web.waitForText('Skorzystałeś z pakietu, żeby opublikować ogłoszenie!');
    }
    if (payment === 'failed') {
      await this.fillCardDetails(paymentCardNumber);
      await this.fillInvoiceInformation();
      await this.submitPayment();
      await this.web.waitForText('Płatność odrzucona.');
    }
  }

  private async fillInvoiceInformation(invoiceInfo?: {countryCode: string, vatId: string}): None {
    await this.web.fillByLabel('Nazwa firmy', 'company name');
    await this.fillInvoiceInformationCountryCode(invoiceInfo?.countryCode ?? 'PL');
    await this.fillInvoiceInformationVatId(invoiceInfo?.vatId ?? '5555666677');
    await this.web.fillByLabel('Adres', 'company address');
    await this.web.fillByLabel('Kod pocztowy', '11-222');
    await this.web.fillByLabel('Miasto', 'company city');
  }

  private async fillInvoiceInformationCountryCode(countryCode: string): None {
    await this.web.selectByLabel('Kraj', countryCode);
  }

  private async fillInvoiceInformationVatId(value: string): None {
    await this.web.fillByLabel('NIP / VAT - ID', value);
  }

  private async fillCardDetails(cardNumber: string): None {
    await this.web.fill('paymentProviderCard', cardNumber);
  }

  async finalizePaymentByUsingBundle(): None {
    await this.web.click('Publikuj korzystając z pakietu');
  }

  private async submitPayment(): None {
    await this.web.click('Zapłać i Publikuj');
  }

  async updateJobOffer(sourceTitle: string, targetTitle: string, targetDescription: string): None {
    await this.web.click(sourceTitle);
    await this.web.click('Edytuj');
    await this.fillJobOfferForm(targetTitle, targetDescription, 'Company name', false);
    await this.web.click('Zapisz');
    await this.web.waitForText('Zaktualizowano ogłoszenie!');
  }

  async searchJobOffers(searchPhrase: string): None {
    await this.web.fillByPlaceholder('Szukaj po tytule', searchPhrase);
    await this.web.clickByTestId('search');
  }

  async listJobOffers(): Promise<string[]> {
    return await this.web.listStringByTestId('jobOfferTitle');
  }

  async listJobOffersMine(): Promise<string[]> {
    await this.web.click('Moje ogłoszenia');
    return await this.web.listStringByTestId('jobOfferTitle');
  }

  async findJobOfferExpiryInDays(jobOfferTitle: string): Promise<number> {
    await this.web.click(jobOfferTitle);
    const expiresInDaysTitle = await this.web.read('jobOfferExpiresInDays');
    return parseInt(expiresInDaysTitle.replace('za ', '').replace(' dni', ''));
  }

  async readPaymentNotification(): Promise<PaymentNotification> {
    await this.web.waitUntilVisible('paymentNotification');
    const notification = await this.web.readTestValue('paymentNotification');
    return notification as PaymentNotification;
  }

  async readPaymentStatus(): Promise<PaymentStatus> {
    await this.web.waitUntilVisible('paymentStatus');
    const status = await this.web.read('paymentStatus');
    if (status === 'Płatność zaksięgowana!') {
      return 'paymentComplete';
    }
    if (status === 'Płatność odrzucona.') {
      return 'paymentFailed';
    }
    throw new Error('Failed to parse payment status.');
  }

  async findPlanBundle(): Promise<PlanBundle> {
    return this.parsePlanBundle(await this.web.read('planBundle'));
  }

  private parsePlanBundle(planBundle: string): PlanBundle {
    const match = planBundle.match(/^Pozostało (\d+) ogłoszeń z Pakietu (\w+)\.$/);
    if (!match) {
      throw new Error('Failed to parse plan bundle.');
    }
    return {
      bundleName: match[2].toLowerCase() as PricingBundleName,
      remainingJobOffers: parseInt(match[1], 10),
    };
  }

  async hasPlanBundle(): Promise<boolean> {
    return await this.web.isVisible('planBundle');
  }

  async findJobOfferField(jobOfferTitle: string, field: 'description'|'companyName'): Promise<string> {
    await this.web.click(jobOfferTitle);
    if (field === 'description') {
      return await this.web.read('jobOfferDescription');
    }
    if (field === 'companyName') {
      return await this.web.read('jobOfferCompanyName');
    }
    throw new Error('Failed to find job offer field.');
  }

  async tryPublishJobOffer(mode: JobOfferSubmitAttemptMode): Promise<void> {
    await this.web.click('Dodaj ogłoszenie od 0zł');
    await this.selectPricingPlan('free');
    if (mode === 'empty-title') {
      await this.fillJobOfferCompanyName('company name');
      await this.fillJobOfferDetails('', 'description', false);
    }
    if (mode === 'empty-company-name') {
      await this.fillJobOfferCompanyName('');
    }
  }

  async findErrorMessage(): Promise<string> {
    const errorMessage = await this.web.read('errorMessage');
    if (errorMessage === 'Podaj nazwę firmy.') {
      return 'provide-company-name';
    }
    if (errorMessage === 'Podaj tytuł ogłoszenia.') {
      return 'provide-offer-title';
    }
    throw new Error('Failed to parse error message.');
  }

  async anticipatePayment(jobOfferTitle: string, plan: PricingPlan): Promise<void> {
    await this.createJobOffer(jobOfferTitle,
      plan,
      'ignored',
      'description',
      'companyName',
      false);
  }

  async findPaymentSummary(): Promise<PaymentSummary> {
    return {
      plan: parsePricingPlan(await this.web.read('paymentPricingPlan')),
      base: await this.web.read('paymentPriceBase'),
      vat: await this.web.read('paymentPriceVat'),
      total: await this.web.read('paymentPriceTotal'),
    };
  }

  async anticipatePaymentFillInvoice(jobOfferTitle: string, countryCode: string, vatId: string): Promise<void> {
    await this.anticipatePayment(jobOfferTitle, 'premium');
    await this.fillInvoiceInformation({countryCode, vatId});
    // We intentionally didn't fill payment information, in hopes
    // that submitting the payment will yield error messages, that
    // will allow us to assert vat-id. Otherwise, instead of reading
    // the error message, we'd have to assert the validation based
    // on the payment actually being processed, which is more complex.
    await this.submitPayment();
    // The button turned to loading, we wait for it to become active again.
    await this.web.waitForText('Zapłać i Publikuj');
  }

  async findInvoiceVatTaxIncluded(): Promise<'included'|'free'> {
    const priceVat = await this.web.read('paymentPriceVat');
    if (priceVat === '36.57 zł') {
      return 'included';
    }
    if (priceVat === '0 zł') {
      return 'free';
    }
    throw new Error('Failed to parse payment summary vat.');
  }

  async findInvoiceVatIdFieldState(): Promise<'valid'|'invalid'> {
    if (await this.web.isVisibleText('NIP / VAT - ID jest niepoprawny.')) {
      return 'invalid';
    }
    return 'valid';
  }

  async findJobOfferUrl(jobOfferTitle: string): Promise<string> {
    await this.web.click('Moje ogłoszenia');
    await this.web.click(jobOfferTitle);
    return this.web.currentUrl();
  }

  async accessJobOffer(jobOfferUrl: string, jobOfferTitle: string): Promise<boolean> {
    await this.web.openUrl(jobOfferUrl);
    return await this.web.read('jobOfferTitle') === jobOfferTitle;
  }

  async fillJobOfferTechnology(value: string): None {
    await this.web.fillByLabel('Wymagane technologie', value);
  }

  async findAutocompleteValues(): Promise<string[]> {
    return this.web.listStringByTestId('autocompleteValue');
  }

  async markJobOfferAsFavourite(jobOfferTitle: string, favourite: Favourite): Promise<void> {
    await this.locateJobOffer(jobOfferTitle);
    await this.web.clickByTestId('jobOfferFavourite');
    await this.web.click('Wróć do ogłoszeń');
  }

  async findJobOfferFavourite(jobOfferTitle: string): Promise<Favourite> {
    await this.locateJobOffer(jobOfferTitle);
    const checked = await this.findChecked('jobOfferFavourite');
    await this.web.click('Wróć do ogłoszeń');
    return checked ? 'favourite' : 'notFavourite';
  }

  private async findChecked(testId: string): Promise<boolean> {
    return parseChecked(await this.web.readAttribute(testId, 'data-checked'));
  }

  private async locateJobOffer(jobOfferTitle: string): Promise<void> {
    await this.searchJobOffers(jobOfferTitle);
    await this.web.click(jobOfferTitle);
  }

  async trySelectPricingPlan(pricingPlan: PricingPlan): Promise<void> {
    await this.web.click('Dodaj ogłoszenie od 0zł');
    await this.selectPricingPlan(pricingPlan);
  }

  async findAuthenticationState(): Promise<'loggedIn'|'guest'> {
    const loggedIn = await this.web.readTestValue('authenticationState') === 'loggedIn';
    return loggedIn ? 'loggedIn' : 'guest';
  }

  async logout(): Promise<void> {
    await this.web.clickByTestId('authControl');
    await this.web.click('Wyloguj');
    await this.web.waitForTestValue('authenticationState', 'guest')
  }
}

function parseChecked(checked: string|null): boolean {
  if (checked === 'checked') {
    return true;
  }
  if (checked === 'unchecked') {
    return false;
  }
  throw new Error('Failed to parse checked.');
}

function parsePricingPlan(pricingPlan: string): PricingPlan {
  if (pricingPlan === 'Ogłoszenie (30 dni)') return 'premium';
  if (pricingPlan === 'Pakiet 3 ogłoszeń (30 dni)') return 'strategic';
  if (pricingPlan === 'Pakiet 5 ogłoszeń (30 dni)') return 'growth';
  if (pricingPlan === 'Pakiet 20 ogłoszeń (30 dni)') return 'scale';
  throw new Error('Failed to parse a pricing plan.');
}

interface PlanBundle {
  bundleName: PricingBundleName;
  remainingJobOffers: number;
}

type Favourite = 'favourite'|'notFavourite';

type None = Promise<void>;

function parseUrlJobShowId(jobOfferShowUrl: string): number {
  const offerId = jobOfferShowUrl.substring(jobOfferShowUrl.lastIndexOf('/') + 1);
  return parseInt(offerId, 10);
}
