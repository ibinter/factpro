// resources/js/Composables/useAiAssist.js
import { ref } from 'vue'
import axios from 'axios'

export function useAiAssist() {
    const loading = ref(false)
    const error = ref(null)

    async function suggestDescription(name, category = '') {
        loading.value = true
        error.value = null
        try {
            const { data } = await axios.post('/ai/suggest-description', { name, category })
            return data.description || ''
        } catch (e) {
            error.value = e.response?.data?.error || 'Erreur IA'
            return ''
        } finally {
            loading.value = false
        }
    }

    async function suggestPrice(name, currency = 'XOF') {
        loading.value = true
        try {
            const { data } = await axios.post('/ai/suggest-price', { name, currency })
            return data.price
        } catch { return null }
        finally { loading.value = false }
    }

    async function summarizeDocument(payload) {
        loading.value = true
        try {
            const { data } = await axios.post('/ai/summarize-document', payload)
            return data.summary || ''
        } catch { return '' }
        finally { loading.value = false }
    }

    return { loading, error, suggestDescription, suggestPrice, summarizeDocument }
}
