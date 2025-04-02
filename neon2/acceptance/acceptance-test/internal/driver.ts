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
    await this.finalizeJobOfferPayment(pricingType);
  }

  private async finalizeJobOfferPayment(pricingType: PricingType): None {
    if (pricingType === 'paid') {
      await this.waitForText('Dodano ofertę pracy!');
      await this.waitForText('Oferta została stworzona, zostanie opublikowana kiedy zaksięgujemy płatność.');
      await this.waitForText('Zostaniesz przekierowany do formularza płatności online.');
      await this.navigateToHome();
    } else {
      await this.waitForText('Dodano ofertę pracy!');
    }
  }

  private async navigateToHome(): None {
    await this.web.click('> Wróć');
  }

  async updateJobOffer(sourceTitle: string, targetTitle: string): None {
    await this.web.click(sourceTitle);
    await this.web.fillByLabel('Tytuł oferty', targetTitle);
    await this.web.click('Zapisz');
  }

  async searchJobOffers(searchPhrase: string): None {
    await this.web.fillByPlaceholder('Wyszukaj', searchPhrase);
    await this.web.clickByTestId('search');
  }

  async listJobOffers(): Promise<string[]> {
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
