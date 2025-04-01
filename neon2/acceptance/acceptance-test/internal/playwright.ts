import {expect, test as playwrightTest, TestInfo} from '@playwright/test';
import {Driver} from './driver';
import {Dsl} from './dsl';

export const describe = playwrightTest.describe;

export function test(title: string, test: Test): void {
  playwrightTest(title, async function ({page}): Promise<void> {
    await test(new Dsl(new Driver(page)));
  });
}

type Test = (dsl: Dsl) => Promise<void>;

export function beforeEach(block: (dsl: Dsl) => Promise<void>): void {
  playwrightTest.beforeEach(({page}) => block(new Dsl(new Driver(page))));
}

export function assertEquals(expected: any, actual: any): void {
  expect(actual).toStrictEqual(expected);
}

export function assertNotEquals(expected: any, actual: any): void {
  expect(actual).not.toStrictEqual(expected);
}

export function assertThrows(block: Runnable, expectedMessage: string): void {
  expect(block).toThrow(expectedMessage);
}

type Runnable = () => void;

export function assertContains(needle: any, haystack: any[]): void {
  expect(haystack).toContain(needle);
}

export function saveScreenshotAfterFailedTest(filename: string): void {
  playwrightTest.afterEach(async ({page}, testInfo: TestInfo): Promise<void> => {
    if (testInfo.status !== testInfo.expectedStatus) {
      await page.screenshot({
        timeout: 5000,
        path: testInfo.outputPath(filename),
      });
    }
  });
}
