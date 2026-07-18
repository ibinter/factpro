import { usePage } from '@inertiajs/vue3'

export function useTranslation() {
    const page = usePage()

    function t(key, replacements = {}) {
        const translations = page.props.translations || {}
        // key format: 'ui.save', 'documents.invoice'
        const dotIndex = key.indexOf('.')
        if (dotIndex === -1) return key

        const file = key.substring(0, dotIndex)
        const rest = key.substring(dotIndex + 1)
        let value = translations[file]?.[rest] ?? key

        // Replace :placeholder with values
        Object.entries(replacements).forEach(([k, v]) => {
            value = value.replace(`:${k}`, v)
        })

        return value
    }

    const locale = () => page.props.locale || 'fr'
    const isRtl = () => locale() === 'ar'

    return { t, locale, isRtl }
}
