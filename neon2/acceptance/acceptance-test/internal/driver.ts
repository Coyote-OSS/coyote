import {Page} from '@playwright/test';
import {Payment, PricingBundleName, PricingPlan} from './dsl';
import {WebDriver} from './webDriver';

export type PaymentNotification = string;
export type PaymentStatus = string;

export class Driver {
  private web: WebDriver;

  constructor(page: Page) {
    this.web = new WebDriver(page);
  }

  async loadApplication(userId: number): None {
    await this.web.navigate('/?userId=' + userId.toString());
  }

  userId(): number {
    return parseInt(this.web.queryParam('userId'));
  }

  async reloadApplication(): None {
    await this.web.reload();
  }

  async publishJobOffer(
    title: string,
    pricingPlan: PricingPlan,
    payment: Payment,
    paymentCardNumber: string,
    description: string,
    companyName: string,
  ): None {
    await this.createJobOffer(title, pricingPlan, payment, description, companyName);
    if (pricingPlan === 'free') {
      await this.web.waitForText('Dodano ogłoszenie!');
    } else {
      await this.finalizeJobOfferPayment(payment, paymentCardNumber);
    }
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
  ) {
    await this.web.click('Dodaj ogłoszenie');
    if (payment !== 'redeem-bundle') {
      await this.selectPricingPlan(pricingPlan);
    }
    await this.submitJobOfferForm(title, description, companyName);
  }

  async initiatePayment(jobOfferTitle: string, cardNumber: string): None {
    await this.web.click('Dodaj ogłoszenie');
    await this.selectPricingPlan('premium');
    await this.submitJobOfferForm(jobOfferTitle, 'description', 'companyName');
    await this.fillCardDetails(cardNumber);
    await this.proceedCardPayment();
  }

  private async submitJobOfferForm(title: string, description: string, companyName: string): None {
    await this.web.fillByLabel('Tytuł ogłoszenia', title);
    await this.web.fillByLabel('Opis ogłoszenia', description);
    await this.web.fillByLabel('Nazwa firmy', companyName);
    await this.web.click('Dodaj');
    await this.web.waitForText('Dodano ogłoszenie!');
  }

  private async selectPricingPlan(pricingPlan: PricingPlan): None {
    if (pricingPlan === 'free') {
      await this.web.click('Publikuj ogłoszenie');
    } else if (pricingPlan === 'premium') {
      await this.web.click('Kup ogłoszenie');
    } else if (pricingPlan === 'strategic') {
      await this.web.click('Kup pakiet Strategic');
    } else if (pricingPlan === 'growth') {
      await this.web.click('Kup pakiet Growth');
    } else if (pricingPlan === 'scale') {
      await this.web.click('Kup pakiet Scale');
    } else {
      throw new Error('Failed to select a pricing plan.');
    }
  }

  private async fillCardDetails(cardNumber: string): None {
    await this.web.fill('paymentProviderCard', cardNumber);
  }

  private async finalizeJobOfferPayment(payment: Payment, paymentCardNumber: string): None {
    await this.web.waitForText('Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność.');
    if (payment === 'completed') {
      await this.fillCardDetails(paymentCardNumber);
      await this.proceedCardPayment();
      await this.web.waitForText('Płatność zaksięgowana!');
    }
    if (payment === 'redeem-bundle') {
      await this.finalizePaymentByUsingBundle();
      await this.web.waitForText('Skorzystałeś z pakietu, żeby opublikować ogłoszenie!');
    }
    if (payment === 'failed') {
      await this.fillCardDetails(paymentCardNumber);
      await this.proceedCardPayment();
      await this.web.waitForText('Płatność odrzucona.');
    }
  }

  async finalizePaymentByUsingBundle(): None {
    await this.web.click("Skorzystaj z pakietu by Opublikować");
  }

  private async proceedCardPayment(): None {
    await this.web.click('Zapłać i Publikuj');
  }

  async updateJobOffer(sourceTitle: string, targetTitle: string, targetDescription: string): None {
    await this.web.click(sourceTitle);
    await this.web.click('Edytuj');
    await this.web.fillByLabel('Tytuł ogłoszenia', targetTitle);
    await this.web.fillByLabel('Opis ogłoszenia', targetDescription);
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

  async findJobOfferExpiryInDays(jobOfferTitle: string): Promise<number> {
    await this.web.click(jobOfferTitle);
    return await this.web.readNumberByTestId('jobOfferExpiresInDays');
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
      return await this.web.readStringByTestId('jobOfferDescription');
    }
    if (field === 'companyName') {
      return await this.web.readStringByTestId('jobOfferCompanyName');
    }
    throw new Error('Failed to find job offer field.');
  }
}

interface PlanBundle {
  bundleName: PricingBundleName;
  remainingJobOffers: number;
}

type None = Promise<void>;
