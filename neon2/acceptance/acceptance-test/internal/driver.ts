import {Page} from '@playwright/test';
import {PricingType} from './dsl';
import {WebDriver} from './webDriver';

export class Driver {
  private web: WebDriver;

  constructor(private page: Page) {
    this.web = new WebDriver(page);
  }

  async loadApplication(): None {
    await this.web.navigate('/');
  }

  async publishJobOffer(title: string, pricingType: PricingType): None {
    await this.web.click('Dodaj ofertę');
    if (pricingType === 'free') {
      await this.web.click('Publikuj ogłoszenie');
    } else {
      await this.web.click('Kup ogłoszenie');
    }
    await this.web.fillByLabel('Tytuł oferty', title);
    await this.web.click('Dodaj');
  }

  async updateJobOffer(sourceTitle: string, targetTitle: string): None {
    await this.web.click(sourceTitle);
    await this.web.fillByLabel('Tytuł oferty', targetTitle);
    await this.web.click('Zapisz');
  }

  async searchJobOffers(searchPhrase: string): Promise<string[]> {
    await this.web.fillByPlaceholder('Wyszukaj', searchPhrase);
    await this.web.clickByTestId('search');
    return await this.web.listStringByTestId('jobOfferTitle');
  }

  async findJobOfferExpiryInDays(jobOfferTitle: string): Promise<number> {
    await this.web.click(jobOfferTitle);
    return await this.web.readStringByTestId('jobOfferExpiresInDays');
  }

  async waitForText(text: string): None {
    await this.web.waitForText(text);
  }
}

type None = Promise<void>;
