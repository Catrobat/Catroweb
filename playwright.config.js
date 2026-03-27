const { defineConfig } = require('@playwright/test')

module.exports = defineConfig({
  testDir: './tests/Playwright',
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  globalSetup: require.resolve('./tests/Playwright/global.setup.js'),
  reporter: [
    ['list'],
    ['junit', { outputFile: 'tests/TestReports/Playwright/junit.xml' }],
    ['html', { outputFolder: 'tests/TestReports/Playwright/html', open: 'never' }],
  ],
  outputDir: 'tests/TestReports/Playwright/test-results',
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8080',
    viewport: {
      width: 412,
      height: 823,
    },
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
})
