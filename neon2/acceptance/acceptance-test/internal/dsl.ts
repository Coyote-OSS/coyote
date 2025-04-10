import {Driver, PaymentNotification, PaymentStatus} from './driver';
import {Mangler} from './mangler';
import {assertContains, assertEquals, assertNotContains} from './playwright';

export type PricingType = 'free'|'paid';
export type Payment = 'completed'|'ignored';
export type Card = 'valid'|'declined'|'expired'|'insufficientFunds';

export class Dsl {
  private mangler: Mangler;

  constructor(private driver: Driver) {
    this.mangler = new Mangler();
  }

  async beforeEach(): None {
    await this.driver.loadApplication();
    this.mangler.reset();
  }

  async resetClient(): None {
    await this.driver.loadApplication();
  }

  async publishJobOffer(jobOffer: {title: string, pricingType?: PricingType, payment?: Payment}): None {
    await this.driver.publishJobOffer(
      this.enc(jobOffer.title),
      jobOffer.pricingType || 'free',
      jobOffer.payment || 'completed',
      this.cardNumber('valid'));
  }

  async updateJobOffer(update: {title: string, updatedTitle: string}): None {
    await this.driver.updateJobOffer(this.enc(update.title), this.enc(update.updatedTitle));
  }

  async searchJobOffers(search: {searchPhrase: string}): None {
    await this.driver.searchJobOffers(search.searchPhrase);
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

  async initiatePayment(payment: {card: Card}): None {
    await this.driver.initiatePayment(
      this.enc('Job offer'),
      this.cardNumber(payment.card || 'valid'));
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

  async assertPaymentNotification(assertion: {expectedPaymentNotification: PaymentNotification}): None {
    assertEquals(assertion.expectedPaymentNotification,
      await this.driver.readPaymentNotification());
  }

  async assertPaymentStatus(assertion: {expectedPaymentStatus: PaymentStatus}): None {
    assertEquals(assertion.expectedPaymentStatus,
      await this.driver.readPaymentStatus());
  }
}

type None = Promise<void>;
