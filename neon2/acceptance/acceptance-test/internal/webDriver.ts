import {Page} from '@playwright/test';

export class WebDriver {
  constructor(private page: Page) {
  }

  async navigate(url: string): None {
    await this.page.goto(url);
  }

  queryParam(queryParam: string): string {
    const url = new URL(this.page.url());
    return url.searchParams.get(queryParam);
  }

  async reload(): None {
    await this.page.reload();
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

  async readNumberByTestId(testId: string): Promise<number> {
    const text = await this.page.getByTestId(testId).textContent();
    if (text) {
      return parseInt(text);
    }
    throw new Error(`Failed to read a number by test id: ${testId}`);
  }

  async readStringByTestId(testId: string): Promise<string> {
    const text = await this.page.getByTestId(testId).textContent();
    if (text) {
      return text.trim();
    }
    throw new Error(`Failed to read a string by test id: ${testId}`);
  }

  async listStringByTestId(testId: string): Promise<string[]> {
    return this.page.getByTestId(testId).allTextContents();
  }

  async waitForText(text: string): None {
    // Locally, timeout of 400ms is sufficient, but occasionally
    // tests fail on github actions because of the timeout,
    // thus timeout of 1250ms is used.
    await this.page.getByText(text).waitFor({timeout: 1250});
  }

  async waitUntilVisible(testId: string): None {
    await this.page.getByTestId(testId).waitFor({state: 'visible', timeout: 7500});
  }

  async read(testId: string): Promise<string> {
    const string = await this.page.getByTestId(testId).textContent();
    return string!;
  }

  async isVisible(testId: string): Promise<boolean> {
    return await this.page.getByTestId(testId).isVisible();
  }

  async readTestValue(testId: string): Promise<string|null> {
    return await this.page.getByTestId(testId).getAttribute('data-test-value');
  }

  async fill(testId: string, value: string): None {
    await this.page.getByTestId(testId).fill(value);
  }
}

type None = Promise<void>;
