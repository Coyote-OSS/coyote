import {Driver, PaymentNotification, PaymentStatus} from './driver';
import {HttpDriver} from "./httpDriver";
import {Mangler} from './mangler';
import {assertContains, assertEquals, assertNotContains} from './playwright';

export type PricingType = 'free'|'paid';
export type Payment = 'completed'|'ignored'|'redeem-bundle'|'failed';
export type Card = 'valid'|'declined'|'expired'|'insufficientFunds';
export type PricingBundleName = 'strategic'|'growth'|'scale';
export type PricingPlan = 'free'|'premium'|PricingBundleName;
export type PaymentMethod = 'card'|'p24';
export type JobOfferSubmitAttemptMode = 'empty-title'|'empty-company-name';

export interface PaymentSummary {
  plan: PricingPlan;
  base: string;
  vat: string;
  total: string;
}

export class Dsl {
  private mangler: Mangler;

  constructor(private driver: Driver, public http: HttpDriver|null) {
    this.mangler = new Mangler();
  }

  async beforeEach(): None {
    this.mangler.invalidate();
  }

  userId(): number {
    return this.driver.userId();
  }

  async resetClient(): None {
    await this.driver.reloadApplication();
  }

  async publishJobOffer(jobOffer: {
    title: string,
    plan?: PricingPlan,
    pricingType?: PricingType,
    payment?: Payment,
    description?: string,
    companyName?: string,
  }): None {
    await this.driver.publishJobOffer(
      this.enc(jobOffer.title),
      this.pricingPlan(jobOffer.plan, jobOffer.pricingType, jobOffer.payment),
      jobOffer.payment || 'completed',
      this.cardPaymentFor(jobOffer.payment),
      jobOffer.description || 'Description',
      jobOffer.companyName || 'Company name');
  }

  async finishPayment(): None {
    await this.driver.finishPayment(this.cardNumber('valid'));
  }

  private pricingPlan(plan?: PricingPlan, pricingType?: PricingType, payment?: Payment): PricingPlan {
    if (plan) {
      return plan;
    }
    if (payment) {
      return 'premium';
    }
    return pricingType === 'paid' ? 'premium' : 'free';
  }

  private cardPaymentFor(payment?: Payment): string {
    if (payment === 'failed') {
      return this.cardNumber('declined');
    }
    return this.cardNumber('valid');
  }

  async updateJobOffer(update: {
    title: string,
    updatedTitle?: string,
    updatedDescription?: string
  }): None {
    await this.driver.updateJobOffer(
      this.enc(update.title),
      this.enc(update.updatedTitle || update.title),
      update.updatedDescription || 'Updated description',
    );
  }

  async searchJobOffers(search: {searchPhrase: string}): None {
    await this.driver.searchJobOffers(search.searchPhrase);
  }

  async purchasePlanBundle(bundle: {plan: PricingPlan, remaining: number}): None {
    if (bundle.plan === 'strategic' && bundle.remaining === 2) {
      await this.publishJobOffer({
        title: 'Purchase a bundle',
        plan: 'strategic',
      });
    } else {
      throw new Error('Failed to initialize a pricing plan bundle.');
    }
  }

  async initiatePayment(payment: {paymentMethod?: PaymentMethod, card?: Card}): None {
    if (payment.paymentMethod === 'p24') {
      await this.driver.initiateP24Payment(this.enc('Job offer'));
    } else {
      await this.driver.initiateCardPayment(
        this.enc('Job offer'),
        this.cardNumber(payment.card || 'valid'));
    }
  }

  private cardNumber(card: Card): string {
    const cardNumbers: Record<Card, string> = {
      'valid': '4242424242424242',
      'expired': '4000000000000069',
      'insufficientFunds': '4000000000009995',
      'declined': '4000000000000002',
    };
    return cardNumbers[card];
  }

  async tryPublishJobOffer(jobOffer: {mode: JobOfferSubmitAttemptMode}): None {
    await this.driver.tryPublishJobOffer(jobOffer.mode);
  }

  async anticipatePayment(payment: {plan: PricingPlan}): None {
    await this.driver.anticipatePayment(this.enc('Job offer'), payment.plan);
  }

  async assertJobOfferIsListed(assertion: {jobOfferTitle: string}): None {
    assertContains(
      assertion.jobOfferTitle,
      this.mangler.decodedAll(await this.driver.listJobOffers()));
  }

  async assertJobOfferIsNotListed(assertion: {jobOfferTitle: string}): None {
    assertNotContains(
      assertion.jobOfferTitle,
      this.mangler.decodedAll(await this.driver.listJobOffers()));
  }

  async assertJobOfferExpiresInDays(assertion: {jobOfferTitle: string, expectedExpiry: number}): None {
    assertEquals(
      assertion.expectedExpiry,
      await this.driver.findJobOfferExpiryInDays(this.enc(assertion.jobOfferTitle)));
  }

  private enc(name: string): string {
    return this.mangler.encoded(name);
  }

  async assertPaymentNotification(assertion: {expectedPaymentNotification: PaymentNotification}): None {
    assertEquals(assertion.expectedPaymentNotification,
      await this.driver.readPaymentNotification());
  }

  async assertPaymentStatus(assertion: {expectedPaymentStatus: PaymentStatus}): None {
    assertEquals(assertion.expectedPaymentStatus,
      await this.driver.readPaymentStatus());
  }

  async assertPlanBundleRemaining(assertion: {
    expectedRemainingJobOffers: number,
    expectedBundleName: PricingBundleName
  }): None {
    const bundle = await this.driver.findPlanBundle();
    assertEquals(assertion.expectedBundleName, bundle.bundleName);
    assertEquals(assertion.expectedRemainingJobOffers, bundle.remainingJobOffers);
  }

  async assertPlanBundleNone(): None {
    assertEquals(false, await this.driver.hasPlanBundle());
  }

  async assertJobOfferField(assertion: {
    jobOfferTitle: string,
    expectedDescription?: string,
    expectedCompanyName?: string,
  }): None {
    if (assertion.expectedDescription) {
      assertEquals(
        assertion.expectedDescription,
        await this.driver.findJobOfferField(this.enc(assertion.jobOfferTitle), 'description'));
    }
    if (assertion.expectedCompanyName) {
      assertEquals(
        assertion.expectedCompanyName,
        await this.driver.findJobOfferField(this.enc(assertion.jobOfferTitle), 'companyName'));
    }
  }

  async assertErrorMessage(assertion: {expectedErrorMessage: string}): None {
    assertEquals(
      assertion.expectedErrorMessage,
      await this.driver.findErrorMessage());
  }

  async assertPricingPlanCost(assertion: {
    expectedPlan: PricingPlan,
    expectedBase: string,
    expectedVat: string,
    expectedTotal: string,
  }): None {
    const expected: PaymentSummary = {
      plan: assertion.expectedPlan,
      base: assertion.expectedBase,
      vat: assertion.expectedVat,
      total: assertion.expectedTotal,
    };
    assertEquals(expected, await this.driver.findPaymentSummary());
  }

  async fillInvoice(invoice: {countryCode: string, vatId: string,}): None {
    await this.driver.anticipatePaymentFillInvoice(
      this.enc('Job offer'),
      invoice.countryCode,
      invoice.vatId);
  }

  async assertInvoiceVatTax(vat: 'included'|'free'): None {
    assertEquals(vat, await this.driver.findInvoiceVatTaxIncluded());
  }

  async assertInvoiceVatId(state: 'valid'|'invalid'): None {
    assertEquals(state, await this.driver.findInvoiceVatIdFieldState());
  }
}

type None = Promise<void>;
