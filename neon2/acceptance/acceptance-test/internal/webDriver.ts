import {Page} from '@playwright/test';

export class WebDriver {
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

  async readStringByTestId(testId: string): Promise<number> {
    const text = await this.page.getByTestId(testId).textContent();
    if (text) {
      return parseInt(text);
    }
    throw new Error(`Failed to read string by test id: ${testId}`);
  }

  async listStringByTestId(testId: string): Promise<string[]> {
    return this.page.getByTestId(testId).allTextContents();
  }

  async waitForText(text: string): None {
    await this.page.getByText(text).waitFor({timeout: 500});
  }

  async waitUntilVisible(testId: string): None {
    await this.page.getByTestId(testId).waitFor({state: 'visible', timeout: 7500});
  }

  async read(testId: string): Promise<string|null> {
    return await this.page.getByTestId(testId).textContent();
  }

  async readValue(testId: string): Promise<string|null> {
    return await this.page.getByTestId(testId).getAttribute('data-value');
  }

  async fill(testId: string, value: string): None {
    await this.page.getByTestId(testId).fill(value);
  }
}

type None = Promise<void>;
