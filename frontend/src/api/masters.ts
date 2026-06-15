// src/api/masters.ts

import client from './client'
import type { Master, MasterSearchParams, MasterSearchResult, SlotsByDate } from '@/types/master.types'

export const mastersApi = {
  search(params: MasterSearchParams): Promise<MasterSearchResult> {
    return client
        .get<MasterSearchResult>('/api/v1/masters/search', { params })
        .then(r => r.data)
  },

  getProfile(id: number): Promise<{ data: Master }> {
    return client
        .get<{ data: Master }>(`/api/v1/masters/${id}`)
        .then(r => r.data)
  },

  getSlots(
      masterId: number,
      params: { dateFrom: string; dateTo: string; serviceId?: number }
  ): Promise<{ data: SlotsByDate }> {
    return client
        .get<{ data: SlotsByDate }>(`/api/v1/masters/${masterId}/slots`, { params })
        .then(r => r.data)
  },
}