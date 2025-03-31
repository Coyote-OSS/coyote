import {Locator, Page} from '@playwright/test';
import {assertContains} from './playwright';

type None = Promise<void>;

export class Dsl {
  constructor(private driver: Driver) {
  }

  async publishJobOffer(jobOffer: { title: string; }): None {
    await this.driver.publishJobOffer(jobOffer.title);
  }

  async updateJobOffer(update: { title: string, updatedTitle: string }): None {
    await this.driver.updateJobOffer(update.title, update.updatedTitle);
  }

  async assertJobOfferIsSearchable(assertion: { jobOfferTitle: string }): None {
    assertContains(
      assertion.jobOfferTitle,
      await this.driver.searchJobOffers(assertion.jobOfferTitle));
  }
}

export class Driver {
  private web: WebDriver;

  constructor(private page: Page) {
    this.web = new WebDriver(page);
  }

  async loadApplication(): None {
    await this.web.navigate('/');
  }

  async publishJobOffer(title: string): None {
    await this.web.click('Dodaj ofertę');
    await this.web.click('Publikuj ogłoszenie');
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
}

class WebDriver {
  constructor(private page: Page) {
  }

  async navigate(url: string): None {
    await this.page.goto(url);
  }

  async click(text: string): None {
    await this.page.getByText(text, {exact: true}).click();
  }

  async clickByTestId(testId: string): None {
    await this.page.getByTestId(testId).click();
  }

  async fillByLabel(label: string, value: string): None {
    await this.page.getByLabel(label).fill(value);
  }

  async fillByPlaceholder(placeholder: string, value: string): None {
    await this.page.getByPlaceholder(placeholder).fill(value);
  }

  async listStringByTestId(testId: string): Promise<string[]> {
    return await this.listStrings(this.page.getByTestId(testId), `Failed to read string by test id: ${testId}`);
  }

  private async listStrings(rows: Locator, errorMessage: string): Promise<string[]> {
    const strings: string[] = [];
    const count = await rows.count();
    for (let i = 0; i < count; ++i) {
      const text = await rows.nth(i).textContent();
      if (text === null) {
        throw new Error(errorMessage);
      }
      strings.push(text);
    }
    return strings;
  }
}
