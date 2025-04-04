import {Driver} from './driver';
import {Mangler} from './mangler';
import {assertContains, assertEquals, assertNotContains} from './playwright';

export type PricingType = 'free'|'paid';
export type Payment = 'completed'|'ignored';

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

  async publishJobOffer(jobOffer: { title: string, pricingType?: PricingType, payment?: Payment }): None {
    await this.driver.publishJobOffer(this.enc(jobOffer.title), jobOffer.pricingType || 'free', jobOffer.payment || 'completed');
  }

  async updateJobOffer(update: { title: string, updatedTitle: string }): None {
    await this.driver.updateJobOffer(this.enc(update.title), this.enc(update.updatedTitle));
  }

  async searchJobOffers(search: { searchPhrase: string }): None {
    await this.driver.searchJobOffers(search.searchPhrase);
  }

  async assertJobOfferIsListed(assertion: { jobOfferTitle: string }): None {
    assertContains(
      assertion.jobOfferTitle,
      this.mangler.decodedAll(await this.driver.listJobOffers()));
  }

  async assertJobOfferIsNotListed(assertion: { jobOfferTitle: string }): None {
    assertNotContains(
      assertion.jobOfferTitle,
      this.mangler.decodedAll(await this.driver.listJobOffers()));
  }

  async assertJobOfferInMyOffers(assertion: { jobOfferTitle: string }): None {
    assertContains(
      assertion.jobOfferTitle,
      this.mangler.decodedAll(await this.driver.listJobOffersMine()));
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
