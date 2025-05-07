import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
  testDir: './test',
  fullyParallel: true,
  use: {
    baseURL: 'http://nginx/Praca',
  },
  timeout: 60000,
  projects: [
    {name: 'Google Chrome', use: {...devices['Desktop Chrome'], channel: 'chrome'}},
  ],
});
