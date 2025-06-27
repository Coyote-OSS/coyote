import {expect, Page} from '@playwright/test';

export class WebDriver {
  constructor(private page: Page) {
  }

  currentUrl(): string {
    return this.page.url();
  }

  currentUrlPath(): string {
    return new URL(this.currentUrl()).pathname;
  }

  async openUrl(url: string): Promise<void> {
    await this.page.goto(url);
  }

  async setAcceptanceTags(tagNames: string[]): None {
    await this.page.goto('/Job?' + new URLSearchParams({
      acceptanceTagNames: JSON.stringify(tagNames),
    }));
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

  async selectByLabel(label: string, option: string): None {
    await this.page.getByLabel(label).selectOption(option);
  }

  async fillByPlaceholder(placeholder: string, value: string): None {
    await this.page.getByPlaceholder(placeholder).fill(value);
  }

  async read(testId: string): Promise<string> {
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
    await this.page.getByText(text).waitFor({state: 'visible'});
  }

  async waitUntilVisible(testId: string): None {
    await this.page.getByTestId(testId).waitFor({state: 'visible'});
  }

  async isVisible(testId: string): Promise<boolean> {
    return await this.page.getByTestId(testId).isVisible();
  }

  async isVisibleText(text: string): Promise<boolean> {
    return await this.page.getByText(text).isVisible();
  }

  async readTestValue(testId: string): Promise<string|null> {
    return await this.page.getByTestId(testId).getAttribute('data-test-value');
  }

  async waitForTestValue(testId: string, expectedValue: string): Promise<void> {
    const locator = this.page.getByTestId(testId);
    await expect(locator).toHaveAttribute('data-test-value', expectedValue);
  }

  async fill(testId: string, value: string): None {
    await this.page.getByTestId(testId).fill(value);
  }

  async readAttribute(testId: string, attributeName: string): Promise<string|null> {
    return await this.page.getByTestId(testId).getAttribute(attributeName);
  }
}

type None = Promise<void>;
