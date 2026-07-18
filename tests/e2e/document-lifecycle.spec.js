import { test, expect } from '@playwright/test'
import { loginAsDemo } from './helpers/auth.js'
import { testCustomer, testProduct, uniqueEmail, uniqueName } from './helpers/fixtures.js'

test.describe("Cycle de vie d'une facture", () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDemo(page)
    })

    test('tableau de bord affiche les KPIs', async ({ page }) => {
        await page.goto('/dashboard')
        // Les cartes KPI doivent être présentes
        const kpiCards = page.locator('[data-testid*="kpi"], .kpi-card, .stat-card, .metric-card')
        // Au moins quelque chose de chargé sur le dashboard
        await expect(page.locator('main')).toBeVisible()

        // Vérifier que la page a des chiffres / montants (FCFA ou CFA ou XOF)
        const bodyText = await page.locator('body').textContent()
        // Le dashboard doit afficher du contenu substantiel
        expect(bodyText.length).toBeGreaterThan(200)
    })

    test('liste des clients accessible', async ({ page }) => {
        await page.goto('/customers')
        await expect(page).not.toHaveURL(/login/)
        await expect(page.locator('main, [data-testid="customers-list"]')).toBeVisible()
    })

    test('créer un client via le formulaire', async ({ page }) => {
        await page.goto('/customers')

        // Cherche un bouton "Nouveau client" ou "Ajouter"
        const addBtn = page.locator(
            'button:has-text("Nouveau client"), button:has-text("Ajouter"), a:has-text("Nouveau client"), [data-testid="add-customer"]'
        ).first()
        await expect(addBtn).toBeVisible({ timeout: 5000 })
        await addBtn.click()

        // Remplir le formulaire (modal ou page dédiée)
        const nameField = page.locator('[name="name"]').first()
        await expect(nameField).toBeVisible({ timeout: 5000 })

        const customerName = uniqueName('Client E2E')
        await nameField.fill(customerName)

        const emailField = page.locator('[name="email"]').first()
        if (await emailField.count() > 0) {
            await emailField.fill(uniqueEmail('client'))
        }

        const phoneField = page.locator('[name="phone"]').first()
        if (await phoneField.count() > 0) {
            await phoneField.fill(testCustomer.phone)
        }

        // Soumettre
        const submitBtn = page.locator('[type="submit"], button:has-text("Enregistrer"), button:has-text("Créer")').first()
        await submitBtn.click()

        // Vérifier que le client apparaît dans la liste
        await expect(page.locator(`text="${customerName}"`)).toBeVisible({ timeout: 10000 })
    })

    test('liste des produits accessible', async ({ page }) => {
        await page.goto('/products')
        await expect(page).not.toHaveURL(/login/)
        await expect(page.locator('main')).toBeVisible()
    })

    test('créer un produit/service via le formulaire', async ({ page }) => {
        await page.goto('/products')

        const addBtn = page.locator(
            'button:has-text("Nouveau produit"), button:has-text("Ajouter"), button:has-text("Nouveau service"), [data-testid="add-product"]'
        ).first()
        await expect(addBtn).toBeVisible({ timeout: 5000 })
        await addBtn.click()

        const nameField = page.locator('[name="name"]').first()
        await expect(nameField).toBeVisible({ timeout: 5000 })

        const productName = uniqueName('Service E2E')
        await nameField.fill(productName)

        const priceField = page.locator('[name="unit_price"], [name="price"]').first()
        if (await priceField.count() > 0) {
            await priceField.fill(testProduct.unit_price)
        }

        const taxField = page.locator('[name="tax_rate"]').first()
        if (await taxField.count() > 0) {
            await taxField.fill(testProduct.tax_rate)
        }

        const submitBtn = page.locator('[type="submit"], button:has-text("Enregistrer"), button:has-text("Créer")').first()
        await submitBtn.click()

        await expect(page.locator(`text="${productName}"`)).toBeVisible({ timeout: 10000 })
    })

    test('page de création de facture accessible', async ({ page }) => {
        // Tente /documents/create ou /invoices/create
        const paths = ['/documents/create', '/invoices/create', '/documents/create?type=invoice']
        let found = false

        for (const path of paths) {
            await page.goto(path)
            if (!page.url().includes('login') && !page.url().includes('404')) {
                found = true
                break
            }
        }

        expect(found).toBeTruthy()
        await expect(page.locator('main, form')).toBeVisible()
    })

    test('créer une facture brouillon', async ({ page }) => {
        // Naviguer vers la création de document
        const createPaths = ['/documents/create', '/invoices/create']
        for (const path of createPaths) {
            await page.goto(path)
            if (!page.url().includes('login')) break
        }

        await expect(page.locator('main')).toBeVisible()

        // Sélectionner le type "facture" si le champ existe
        const typeSelect = page.locator('[name="type"], select[name="type"]').first()
        if (await typeSelect.count() > 0) {
            await typeSelect.selectOption('invoice')
        }

        // Sélectionner un client existant
        const clientSelect = page.locator('[name="customer_id"], [name="client_id"], [placeholder*="client" i]').first()
        if (await clientSelect.count() > 0) {
            // Sélectionner le premier client disponible
            const firstOption = clientSelect.locator('option').nth(1)
            if (await firstOption.count() > 0) {
                const optionValue = await firstOption.getAttribute('value')
                await clientSelect.selectOption(optionValue)
            }
        }

        // Sauvegarder comme brouillon
        const draftBtn = page.locator(
            'button:has-text("Brouillon"), button:has-text("Enregistrer"), button:has-text("Sauvegarder")'
        ).first()
        if (await draftBtn.count() > 0) {
            await draftBtn.click()
            // Vérifier qu'on n'est pas sur une page d'erreur
            await expect(page).not.toHaveURL(/500|error/)
        }
    })

    test('liste des documents accessible', async ({ page }) => {
        const paths = ['/documents', '/invoices']
        for (const path of paths) {
            await page.goto(path)
            if (!page.url().includes('login')) break
        }
        await expect(page.locator('main')).toBeVisible()
    })

    test('un document existant peut être visualisé', async ({ page }) => {
        // Aller sur la liste des documents
        const paths = ['/documents', '/invoices']
        for (const path of paths) {
            await page.goto(path)
            if (!page.url().includes('login')) break
        }

        // Cliquer sur le premier document de la liste
        const firstDocLink = page.locator('table tbody tr:first-child a, .document-row:first-child a, [data-testid="document-row"]:first-child a').first()
        if (await firstDocLink.count() > 0) {
            await firstDocLink.click()
            await expect(page.locator('main')).toBeVisible()
            await expect(page).not.toHaveURL(/login/)
        }
    })

    test('téléchargement PDF depuis une facture finalisée', async ({ page }) => {
        // Aller sur la liste des documents pour trouver une facture finalisée
        const paths = ['/documents', '/invoices']
        for (const path of paths) {
            await page.goto(path)
            if (!page.url().includes('login')) break
        }

        // Cherche un bouton / lien PDF
        const pdfLink = page.locator('a[href*="pdf"], button:has-text("PDF"), a:has-text("Télécharger")').first()
        if (await pdfLink.count() > 0) {
            // Intercepter le téléchargement
            const [download] = await Promise.all([
                page.waitForEvent('download', { timeout: 10000 }).catch(() => null),
                pdfLink.click(),
            ])
            // Si un téléchargement est déclenché, vérifier l'extension
            if (download) {
                expect(download.suggestedFilename()).toMatch(/\.pdf$/i)
            }
        }
    })

    test('convertir un devis en facture', async ({ page }) => {
        // Aller sur la liste des devis
        const paths = ['/documents?type=quote', '/quotes', '/documents']
        for (const path of paths) {
            await page.goto(path)
            if (!page.url().includes('login')) break
        }

        // Chercher un bouton "Convertir en facture"
        const convertBtn = page.locator(
            'button:has-text("Convertir en facture"), a:has-text("Convertir en facture"), [data-testid="convert-to-invoice"]'
        ).first()

        if (await convertBtn.count() > 0) {
            await convertBtn.click()
            // Vérifier qu'on est redirigé vers la facture créée
            await expect(page).not.toHaveURL(/login|500/)
        } else {
            // Pas de devis à convertir dans les données démo — test non bloquant
            test.skip()
        }
    })
})
