import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false, // Séquentiel pour éviter les conflits BDD
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: [['html', { outputFolder: 'playwright-report' }], ['list']],
    use: {
        baseURL: process.env.APP_URL || 'http://localhost:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    // Démarrer le serveur Laravel automatiquement si non lancé
    webServer: {
        command: 'php artisan serve --port=8000',
        url: 'http://localhost:8000',
        reuseExistingServer: !process.env.CI,
        timeout: 120000,
    },
})
