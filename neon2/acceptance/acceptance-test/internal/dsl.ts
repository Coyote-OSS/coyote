import {Driver} from './driver';
import {Mangler} from './mangler';
import {assertContains, assertEquals} from './playwright';

export type PricingType = 'free'|'paid';

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

  async publishJobOffer(jobOffer: { title: string, pricingType?: PricingType }): None {
    await this.driver.publishJobOffer(this.enc(jobOffer.title), jobOffer.pricingType || 'free');
    await this.driver.waitForText('Dodano ofertę pracy!');
  }

  async updateJobOffer(update: { title: string, updatedTitle: string }): None {
    await this.driver.updateJobOffer(this.enc(update.title), this.enc(update.updatedTitle));
    await this.driver.waitForText('Zaktualizowano ofertę pracy!');
  }

  async assertJobOfferIsSearchable(assertion: { jobOfferTitle: string }): None {
    assertContains(
      assertion.jobOfferTitle,
      this.mangler.decodedAll(await this.driver.searchJobOffers(this.enc(assertion.jobOfferTitle))));
  }

  async assertJobOfferExpiresInDays(assertion: { jobOfferTitle: string, expectedExpiry: number }): None {
    assertEquals(
      assertion.expectedExpiry,
      await this.driver.findJobOfferExpiryInDays(this.enc(assertion.jobOfferTitle)));
  }

  private enc(name: string): string {
    return this.mangler.encoded(name);
  }
}

type None = Promise<void>;
