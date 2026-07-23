import { ref, watch, onMounted } from 'vue'

export function useWidgetData (type, period, extraParams = {}) {
  const data    = ref(null)
  const loading = ref(false)
  const error   = ref(null)

  async function load () {
    loading.value = true
    error.value   = null
    try {
      const params = new URLSearchParams({ type, period: period.value, ...extraParams })
      const res    = await fetch(`/analytics/data?${params}`)
      if (!res.ok) throw new Error('Erreur réseau')
      data.value = await res.json()
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  onMounted(load)
  watch(period, load)

  return { data, loading, error, reload: load }
}
