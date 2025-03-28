import {expect, test} from '@playwright/test';

test('Acceptance test', async ({page}) => {
  await page.goto('/');
  await expect(page.getByText('Hello, world!')).toBeVisible();
});
