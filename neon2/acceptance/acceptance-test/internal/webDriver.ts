import {Locator, Page} from '@playwright/test';

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

  async waitForText(text: string): None {
    await this.page.getByText(text).waitFor({timeout: 500});
  }
}

type None = Promise<void>;
