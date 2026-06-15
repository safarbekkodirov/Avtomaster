// src/composables/useSearch.ts

import { ref, computed, watch } from 'vue'
import { useMasterStore } from '@/stores/master.store'
import { useGeolocation } from './useGeolocation'
import type { MasterSearchParams } from '@/types/master.types'

export function useSearch(initialParams: MasterSearchParams = {}) {
    const store  = useMasterStore()
    const geo    = useGeolocation()

    const params = ref<MasterSearchParams>({
        page:    1,
        perPage: 20,
        sortBy:  'rating',
        ...initialParams,
    })

    async function applyGeo(): Promise<void> {
        try {
            const coords      = await geo.detect()
            params.value.lat  = coords.lat
            params.value.lng  = coords.lng
            params.value.sortBy = 'distance'
        } catch {
            // Геолокация недоступна — продолжаем без неё
        }
    }

    async function run(): Promise<void> {
        params.value.page = 1
        await store.search(params.value)
    }

    async function loadMore(): Promise<void> {
        if (!store.hasMore || store.loading) return
        params.value.page = (params.value.page ?? 1) + 1
        await store.search(params.value, true)
    }

    function updateFilter(patch: Partial<MasterSearchParams>): void {
        Object.assign(params.value, patch)
        params.value.page = 1
    }

    return {
        params,
        masters:   computed(() => store.masters),
        loading:   computed(() => store.loading),
        hasMore:   store.hasMore,
        applyGeo,
        run,
        loadMore,
        updateFilter,
    }
}