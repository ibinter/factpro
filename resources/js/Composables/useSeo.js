// Retourne les props Head Inertia pour le SEO
export function useSeo({ title, description, image, url, type = 'website' }) {
    const siteName = 'IBIG FactPro';
    const baseUrl  = 'https://factpro.ibigsoft.com';
    const defaultImage = `${baseUrl}/images/og-factpro.png`;

    return {
        title: title ? `${title} — ${siteName}` : siteName,
        meta: [
            // Standard
            { name: 'description', content: description },
            { name: 'robots', content: 'index, follow' },
            { name: 'author', content: 'IBIG Soft SARL' },
            // Open Graph
            { property: 'og:type', content: type },
            { property: 'og:site_name', content: siteName },
            { property: 'og:title', content: title || siteName },
            { property: 'og:description', content: description },
            { property: 'og:image', content: image || defaultImage },
            { property: 'og:url', content: url || baseUrl },
            { property: 'og:locale', content: 'fr_FR' },
            // Twitter Card
            { name: 'twitter:card', content: 'summary_large_image' },
            { name: 'twitter:title', content: title || siteName },
            { name: 'twitter:description', content: description },
            { name: 'twitter:image', content: image || defaultImage },
        ],
    };
}
