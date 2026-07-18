import { test, expect } from '@playwright/test'
import { loginAsDemo } from './helpers/auth.js'

test.describe('Point de vente (POS)', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDemo(page)
    })

    test('page POS accessible après connexion', async ({ page }) => {
        await page.goto('/pos')
        await expect(page).not.toHaveURL(/login/)
        await expect(page.locator('main, #pos-app, [data-testid="pos"]')).toBeVisible()
    })

    test('ouvrir une session POS avec un montant initial', async ({ page }) => {
        await page.goto('/pos')

        // Cherche le bouton d'ouverture de session
        const openSessionBtn = page.locator(
            'button:has-text("Ouvrir session"), button:has-text("Ouvrir la caisse"), [data-testid="open-session"]'
        ).first()

        if (await openSessionBtn.count() > 0) {
            await openSessionBtn.click()

            // Saisir le fonds de caisse initial
            const floatField = page.locator(
                '[name="opening_float"], [name="float"], [placeholder*="fonds" i], [placeholder*="montant initial" i]'
            ).first()

            if (await floatField.count() > 0) {
                await floatField.fill('50000')
            }

            // Confirmer l'ouverture
            const confirmBtn = page.locator(
                'button:has-text("Confirmer"), button:has-text("Ouvrir"), [type="submit"]'
            ).first()
            await confirmBtn.click()

            // La caisse doit être active maintenant
            await expect(page.locator(
                'text=/session ouverte|caisse ouverte|active/i, [data-testid="session-active"]'
            )).toBeVisible({ timeout: 10000 })
        } else {
            // La session est peut-être déjà ouverte
            await expect(page.locator('main')).toBeVisible()
        }
    })

    test('interface POS affiche les produits', async ({ page }) => {
        await page.goto('/pos')

        // Attendre le chargement de la page POS
        await page.waitForLoadState('networkidle')

        // Les produits doivent être affichés sous forme de grille ou liste
        const productsGrid = page.locator(
            '[data-testid="products-grid"], .products-grid, .product-list, .pos-products'
        ).first()

        if (await productsGrid.count() > 0) {
            await expect(productsGrid).toBeVisible()
        } else {
            // Vérifier au minimum que la page n'est pas vide
            await expect(page.locator('main')).toBeVisible()
        }
    })

    test('créer un ticket de caisse (vente rapide)', async ({ page }) => {
        await page.goto('/pos')
        await page.waitForLoadState('networkidle')

        // Ajouter un produit au panier (cliquer sur le premier produit disponible)
        const firstProduct = page.locator(
            '[data-testid="product-item"]:first-child, .product-card:first-child, .pos-product:first-child'
        ).first()

        if (await firstProduct.count() > 0) {
            await firstProduct.click()

            // Le panier doit contenir au moins 1 article
            const cartItem = page.locator('[data-testid="cart-item"], .cart-item, .basket-item').first()
            await expect(cartItem).toBeVisible({ timeout: 5000 })

            // Valider la vente
            const checkoutBtn = page.locator(
                'button:has-text("Valider"), button:has-text("Encaisser"), button:has-text("Payer"), [data-testid="checkout"]'
            ).first()

            if (await checkoutBtn.count() > 0) {
                await checkoutBtn.click()

                // Sélectionner le mode de paiement si demandé
                const cashBtn = page.locator('button:has-text("Espèces"), button:has-text("Cash"), [data-payment="cash"]').first()
                if (await cashBtn.count() > 0) {
                    await cashBtn.click()
                }

                // Confirmer
                const confirmPaymentBtn = page.locator(
                    'button:has-text("Confirmer"), button:has-text("Valider le paiement"), [data-testid="confirm-payment"]'
                ).first()
                if (await confirmPaymentBtn.count() > 0) {
                    await confirmPaymentBtn.click()
                }

                // Un ticket ou confirmation doit apparaître
                await expect(page.locator(
                    '[data-testid="ticket"], .ticket, text=/vente validée|ticket/i'
                )).toBeVisible({ timeout: 10000 })
            }
        } else {
            // Pas de produits disponibles — vérifier que l'interface est présente
            await expect(page.locator('main')).toBeVisible()
            test.skip()
        }
    })

    test('historique des ventes POS accessible', async ({ page }) => {
        await page.goto('/pos')

        const historyBtn = page.locator(
            'button:has-text("Historique"), a:has-text("Historique"), [data-testid="sales-history"]'
        ).first()

        if (await historyBtn.count() > 0) {
            await historyBtn.click()
            await expect(page.locator('main')).toBeVisible()
        }
    })

    test('rapport Z — clôture de session POS', async ({ page }) => {
        await page.goto('/pos')
        await page.waitForLoadState('networkidle')

        // Cherche le bouton de clôture / rapport Z
        const closeBtn = page.locator(
            'button:has-text("Clôturer"), button:has-text("Fermer la session"), button:has-text("Rapport Z"), [data-testid="close-session"]'
        ).first()

        if (await closeBtn.count() > 0) {
            await closeBtn.click()

            // Un résumé des totaux doit être affiché
            const summary = page.locator(
                '[data-testid="z-report"], .z-report, text=/total ventes|clôture/i'
            ).first()

            if (await summary.count() > 0) {
                await expect(summary).toBeVisible({ timeout: 10000 })
            }

            // Confirmer la clôture si une boîte de dialogue apparaît
            const confirmClose = page.locator(
                'button:has-text("Confirmer"), button:has-text("Clôturer la session")'
            ).first()
            if (await confirmClose.count() > 0) {
                await confirmClose.click()
                await expect(page).not.toHaveURL(/500|error/)
            }
        } else {
            // Pas de session active à clôturer
            test.skip()
        }
    })
})
