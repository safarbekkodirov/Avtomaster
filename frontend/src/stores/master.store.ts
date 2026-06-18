import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { mastersApi } from '@/api/masters'
import type { Master, MasterSearchParams, Pagination } from '@/types/master.types'

export const useMasterStore = defineStore('master', () => {
  const masters    = ref<Master[]>([])
  const myProfile  = ref<Master | null>(null)
  const pagination = ref<Pagination | null>(null)
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
      masters.value    = append ? [...masters.value, ...result.data] : result.data
      pagination.value = result.pagination
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Ошибка поиска'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchMyProfile(): Promise<void> {
    loading.value = true
    try {
      myProfile.value = await mastersApi.getMyProfile()
    } catch {
      myProfile.value = null
    } finally {
      loading.value = false
    }
  }

  async function fetchProfile(id: number): Promise<Master> {
    const result = await mastersApi.getProfile(id)
    return result.data
  }

  function reset(): void {
    masters.value    = []
    myProfile.value  = null
    pagination.value = null
    error.value      = null
  }

  return { masters, myProfile, pagination, loading, error, hasMore, search, fetchMyProfile, fetchProfile, reset }
})
