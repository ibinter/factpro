/**
 * Helpers de connexion réutilisables pour les tests E2E Playwright
 * FactPro — Phase 12
 */

/**
 * Se connecte avec les identifiants fournis.
 * @param {import('@playwright/test').Page} page
 * @param {string} email
 * @param {string} password
 */
export async function loginAs(page, email, password) {
    await page.goto('/login')
    await page.fill('[name="email"]', email)
    await page.fill('[name="password"]', password)
    await page.click('[type="submit"]')
    await page.waitForURL('**/dashboard', { timeout: 10000 })
}

/**
 * Connexion avec le compte de démo standard.
 * @param {import('@playwright/test').Page} page
 */
export async function loginAsDemo(page) {
    return loginAs(page, 'demo@factpro.test', 'Demo@2026')
}

/**
 * Connexion avec le compte superadmin.
 * @param {import('@playwright/test').Page} page
 */
export async function loginAsAdmin(page) {
    return loginAs(page, 'admin@ibigsoft.com', 'Admin@Factpro2026')
}

/**
 * Déconnexion : cherche le menu utilisateur et clique sur "Déconnexion".
 * @param {import('@playwright/test').Page} page
 */
export async function logout(page) {
    // Tente plusieurs sélecteurs courants pour le menu utilisateur
    const userMenuSelectors = [
        '[data-testid="user-menu"]',
        '[aria-label="Menu utilisateur"]',
        '.user-menu',
        '#user-dropdown',
        '[data-dropdown="user"]',
    ]

    let menuOpened = false
    for (const selector of userMenuSelectors) {
        const el = page.locator(selector)
        if (await el.count() > 0) {
            await el.click()
            menuOpened = true
            break
        }
    }

    if (!menuOpened) {
        // Fallback : cherche un avatar ou bouton avec le nom de l'utilisateur
        const avatar = page.locator('button:has(img[alt*="avatar" i]), button:has(.avatar), [data-testid="avatar"]').first()
        if (await avatar.count() > 0) {
            await avatar.click()
            menuOpened = true
        }
    }

    // Cherche le lien de déconnexion
    const logoutSelectors = [
        'text=/déconnexion|logout|se déconnecter/i',
        '[data-testid="logout"]',
        'form[action*="logout"] button',
        'a[href*="logout"]',
    ]

    for (const selector of logoutSelectors) {
        const el = page.locator(selector).first()
        if (await el.count() > 0) {
            await el.click()
            break
        }
    }

    await page.waitForURL(/login|home|\/$/, { timeout: 10000 })
}
