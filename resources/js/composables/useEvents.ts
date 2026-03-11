import { ref } from 'vue'
import type { GeoJsonCollection } from '@/types'

export function useEvents() {
    const geojson = ref<GeoJsonCollection | null>(null)
    const loading = ref(false)

    async function fetchGeoJson(params: Record<string, string> = {}) {
        loading.value = true
        try {
            const query = new URLSearchParams(params).toString()
            const url = `/api/events/geojson${query ? '?' + query : ''}`
            const response = await fetch(url)
            geojson.value = await response.json()
        } finally {
            loading.value = false
        }
    }

    return { geojson, loading, fetchGeoJson }
}
