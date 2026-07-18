import { test, expect } from '@playwright/test'
import { loginAs, loginAsDemo, logout } from './helpers/auth.js'
import { uniqueEmail } from './helpers/fixtures.js'

test.describe('Authentification', () => {
    test('page de login se charge correctement', async ({ page }) => {
        await page.goto('/login')
        await expect(page).toHaveTitle(/FactPro|Connexion|Login/i)
        await expect(page.locator('input[name="email"]')).toBeVisible()
        await expect(page.locator('input[name="password"]')).toBeVisible()
        await expect(page.locator('[type="submit"]')).toBeVisible()
    })

    test('connexion avec email et mot de passe valides', async ({ page }) => {
        await loginAsDemo(page)
        await expect(page).toHaveURL(/dashboard/)
        // Vérifier qu'un élément du dashboard est présent
        await expect(page.locator('main, [data-testid="dashboard"], #app')).toBeVisible()
    })

    test('connexion échoue avec un mauvais mot de passe', async ({ page }) => {
        await page.goto('/login')
        await page.fill('[name="email"]', 'demo@factpro.test')
        await page.fill('[name="password"]', 'MauvaisMotDePasse123!')
        await page.click('[type="submit"]')

        // Rester sur la page de login
        await expect(page).toHaveURL(/login/)

        // Un message d'erreur doit être visible
        const errorLocator = page.locator('[role="alert"], .alert, p.text-red-500, .text-red-600, [data-testid="error"]').first()
        await expect(errorLocator).toBeVisible({ timeout: 5000 })
    })

    test('connexion échoue avec un email inexistant', async ({ page }) => {
        await page.goto('/login')
        await page.fill('[name="email"]', 'utilisateur-inexistant@example.com')
        await page.fill('[name="password"]', 'MotDePasse123!')
        await page.click('[type="submit"]')

        await expect(page).toHaveURL(/login/)
        const errorLocator = page.locator('[role="alert"], .alert, p.text-red-500, .text-red-600, [data-testid="error"]').first()
        await expect(errorLocator).toBeVisible({ timeout: 5000 })
    })

    test('validation email requis sur le formulaire de login', async ({ page }) => {
        await page.goto('/login')
        // Soumettre sans remplir les champs
        await page.click('[type="submit"]')
        // La validation HTML5 ou Laravel doit bloquer
        const emailInput = page.locator('input[name="email"]')
        // Soit le champ est invalide (validation HTML5), soit on reste sur la page
        const isInvalid = await emailInput.evaluate(el => !el.validity.valid)
        const urlStillLogin = page.url().includes('login')
        expect(isInvalid || urlStillLogin).toBeTruthy()
    })

    test("inscription d'un nouveau compte", async ({ page }) => {
        await page.goto('/register')
        await expect(page.locator('input[name="email"]')).toBeVisible()

        const email = uniqueEmail('register')
        await page.fill('[name="name"]', 'Test E2E User')
        await page.fill('[name="email"]', email)
        await page.fill('[name="password"]', 'SecurePass@2026')
        await page.fill('[name="password_confirmation"]', 'SecurePass@2026')

        // Le champ company_name peut ne pas exister selon l'implémentation
        const companyField = page.locator('[name="company_name"]')
        if (await companyField.count() > 0) {
            await companyField.fill('Ma Société E2E')
        }

        await page.click('[type="submit"]')
        await expect(page).toHaveURL(/dashboard/, { timeout: 15000 })
    })

    test('déconnexion fonctionne', async ({ page }) => {
        await loginAsDemo(page)
        await expect(page).toHaveURL(/dashboard/)
        await logout(page)
        await expect(page).toHaveURL(/login|home|\/$/)
    })

    test('accès au dashboard sans connexion redirige vers login', async ({ page }) => {
        await page.goto('/dashboard')
        await expect(page).toHaveURL(/login/)
    })

    test('la page de login a un lien vers inscription', async ({ page }) => {
        await page.goto('/login')
        const registerLink = page.locator('a[href*="register"]')
        await expect(registerLink).toBeVisible()
    })
})
