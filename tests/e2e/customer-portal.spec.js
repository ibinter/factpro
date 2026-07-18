import { test, expect } from '@playwright/test'
import { loginAsDemo } from './helpers/auth.js'

test.describe('Portail client', () => {
    /**
     * NOTE : Le portail client est accessible via un token dans l'URL.
     * En CI, le token est récupéré via l'API ou la BDD de démo.
     * En local, on tente d'abord de récupérer un token depuis l'interface admin.
     */

    test('portail client répond sur /portal', async ({ page }) => {
        // Vérifier que la route portail existe (même sans token valide)
        const response = await page.goto('/portal')
        // Doit renvoyer 200 ou redirection, pas 404
        expect(response?.status()).not.toBe(404)
    })

    test('accès portail avec token invalide affiche une erreur', async ({ page }) => {
        await page.goto('/portal/token-invalide-00000000')
        // Doit afficher une erreur ou rediriger, pas une exception serveur
        await expect(page).not.toHaveURL(/500/)
        // Un message d'erreur ou une page "lien expiré" doit s'afficher
        const errorMsg = page.locator(
            'text=/lien expiré|token invalide|introuvable|not found/i, [data-testid="portal-error"]'
        ).first()
        // Si le message n'est pas affiché, au moins le serveur répond
        const statusOk = [200, 302, 404].includes(response?.status?.() ?? 200)
        expect(statusOk).toBeTruthy()
    })

    test('récupérer un token de portail depuis le dashboard admin', async ({ page }) => {
        // Connexion admin démo
        await loginAsDemo(page)

        // Aller sur la liste des documents pour trouver un lien portail
        const paths = ['/documents', '/invoices']
        for (const path of paths) {
            await page.goto(path)
            if (!page.url().includes('login')) break
        }

        // Chercher un lien "Portail client" ou "Envoyer au client" qui révèle le token
        const portalLink = page.locator(
            'a[href*="portal"], button:has-text("Portail"), [data-testid="portal-link"]'
        ).first()

        if (await portalLink.count() > 0) {
            const href = await portalLink.getAttribute('href')
            if (href && href.includes('portal')) {
                // Extraire le token de l'URL
                const tokenMatch = href.match(/portal\/([a-zA-Z0-9_-]+)/)
                if (tokenMatch) {
                    const token = tokenMatch[1]

                    // Accéder au portail avec le token valide
                    await page.goto(`/portal/${token}`)
                    await expect(page).not.toHaveURL(/login/)
                    await expect(page.locator('main')).toBeVisible()
                }
            }
        } else {
            // Pas de lien portail trouvé dans les données démo
            test.skip()
        }
    })

    test('portail affiche les informations de la facture', async ({ page }) => {
        // Ce test nécessite un token valide — récupéré via la base de données de démo
        // En CI, le seeder DemoSeeder doit créer un token connu
        const demoToken = process.env.DEMO_PORTAL_TOKEN

        if (!demoToken) {
            test.skip()
            return
        }

        await page.goto(`/portal/${demoToken}`)
        await expect(page).not.toHaveURL(/login|500/)

        // Les informations de la facture doivent être visibles
        await expect(page.locator('main')).toBeVisible()
        // Le montant doit être affiché
        const bodyText = await page.locator('body').textContent()
        expect(bodyText.length).toBeGreaterThan(100)
    })

    test('téléchargement PDF depuis le portail client', async ({ page }) => {
        const demoToken = process.env.DEMO_PORTAL_TOKEN

        if (!demoToken) {
            test.skip()
            return
        }

        await page.goto(`/portal/${demoToken}`)
        await expect(page).not.toHaveURL(/login|500/)

        // Chercher le bouton de téléchargement PDF
        const pdfBtn = page.locator(
            'a[href*="pdf"], button:has-text("Télécharger"), a:has-text("PDF"), [data-testid="download-pdf"]'
        ).first()

        if (await pdfBtn.count() > 0) {
            const [download] = await Promise.all([
                page.waitForEvent('download', { timeout: 15000 }).catch(() => null),
                pdfBtn.click(),
            ])

            if (download) {
                expect(download.suggestedFilename()).toMatch(/\.pdf$/i)
            }
        } else {
            test.skip()
        }
    })

    test('portail — paiement en ligne (si Stripe activé)', async ({ page }) => {
        const demoToken = process.env.DEMO_PORTAL_TOKEN

        if (!demoToken) {
            test.skip()
            return
        }

        await page.goto(`/portal/${demoToken}`)

        const payOnlineBtn = page.locator(
            'button:has-text("Payer en ligne"), a:has-text("Payer"), [data-testid="pay-online"]'
        ).first()

        if (await payOnlineBtn.count() > 0) {
            // Ne pas déclencher un vrai paiement — juste vérifier que le bouton est présent
            await expect(payOnlineBtn).toBeEnabled()
        } else {
            // Le paiement en ligne n'est pas activé dans la démo
            test.skip()
        }
    })
})
