import {Driver} from './driver';
import {assertContains, assertEquals} from './playwright';

export type PricingType = 'free'|'paid';

export class Dsl {
  constructor(private driver: Driver) {
  }

  async resetClient(): None {
    await this.driver.loadApplication();
  }

  async publishJobOffer(jobOffer: { title: string, pricingType?: PricingType }): None {
    await this.driver.publishJobOffer(jobOffer.title, jobOffer.pricingType || 'free');
    await this.driver.waitForText('Dodano ofertę pracy!');
  }

  async updateJobOffer(update: { title: string, updatedTitle: string }): None {
    await this.driver.updateJobOffer(update.title, update.updatedTitle);
    await this.driver.waitForText('Zaktualizowano ofertę pracy!');
  }

  async assertJobOfferIsSearchable(assertion: { jobOfferTitle: string }): None {
    assertContains(
      assertion.jobOfferTitle,
      await this.driver.searchJobOffers(assertion.jobOfferTitle));
  }

  async assertJobOfferExpiresInDays(assertion: { jobOfferTitle: string, expectedExpiry: number }): None {
    assertEquals(
      assertion.expectedExpiry,
      await this.driver.findJobOfferExpiryInDays(assertion.jobOfferTitle));
  }
}

type None = Promise<void>;
