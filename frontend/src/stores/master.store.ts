// src/stores/master.store.ts

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { mastersApi } from '@/api/masters'
import type { Master, MasterSearchParams, Pagination, SlotsByDate } from '@/types/master.types'

export const useMasterStore = defineStore('master', () => {
  const masters    = ref<Master[]>([])
  const pagination = ref<Pagination | null>(null)
  const slots      = ref<SlotsByDate>({})
  const loading    = ref(false)
  const error      = ref<string | null>(null)

  const hasMore = computed(() =>
      pagination.value !== null &&
      pagination.value.page < pagination.value.totalPages
  )

  async function search(params: MasterSearchParams, append = false): Promise<void> {
    loading.value = true
    error.value   = null

    try {
      const result = await mastersApi.search(params)

      // append=true для infinite scroll / подгрузки следующей страницы
      masters.value    = append ? [...masters.value, ...result.data] : result.data
      pagination.value = result.pagination
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Ошибка поиска'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchSlots(
      masterId: number,
      params: { dateFrom: string; dateTo: string; serviceId?: number }
  ): Promise<void> {
    const result = await mastersApi.getSlots(masterId, params)
    slots.value  = result.data
  }

  function reset(): void {
    masters.value    = []
    pagination.value = null
    slots.value      = {}
    error.value      = null
  }

  return { masters, pagination, slots, loading, error, hasMore, search, fetchSlots, reset }
})