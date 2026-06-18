import client from './client'
import type {
  Master,
  MasterSearchParams,
  MasterSearchResult,
  CreateMasterPayload,
  UpdateMasterPayload,
} from '@/types/master.types'

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

  getMyProfile(): Promise<Master> {
    return client
      .get<Master>('/api/v1/masters/me')
      .then(r => r.data)
  },

  create(payload: CreateMasterPayload): Promise<Master> {
    return client
      .post<Master>('/api/v1/masters', payload)
      .then(r => r.data)
  },

  update(id: number, payload: UpdateMasterPayload): Promise<Master> {
    return client
      .patch<Master>(`/api/v1/masters/${id}`, payload)
      .then(r => r.data)
  },

  addService(masterId: number, service: {
    name: string
    price: number
    durationMinutes: number
    categoryName?: string
  }): Promise<Master> {
    return client
      .post<Master>(`/api/v1/masters/${masterId}/services`, service)
      .then(r => r.data)
  },

  deleteService(masterId: number, serviceId: number): Promise<void> {
    return client
      .delete(`/api/v1/masters/${masterId}/services/${serviceId}`)
      .then(() => undefined)
  },
}
