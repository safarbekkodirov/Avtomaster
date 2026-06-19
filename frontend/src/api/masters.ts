import client from './client'
import type {
  Master,
  MasterSearchParams,
  MasterSearchResult,
  CreateMasterPayload,
  UpdateMasterPayload,
  ServiceCategory,
  Region,
} from '@/types/master.types'

export const mastersApi = {
  search(params: MasterSearchParams): Promise<MasterSearchResult> {
    return client
      .get<MasterSearchResult>('/api/v1/masters/search', { params })
      .then(r => r.data)
  },

  getProfile(id: number): Promise<Master> {
    return client
      .get<Master>(`/api/v1/masters/${id}`)
      .then(r => r.data as any)
  },

  getMyProfile(): Promise<Master> {
    return client
      .get<Master>('/api/v1/masters/me')
      .then(r => r.data)
  },

  getCategories(): Promise<{ member: ServiceCategory[] }> {
    return client
      .get<{ member: ServiceCategory[] }>('/api/v1/service_categories')
      .then(r => r.data)
  },

  getRegions(): Promise<{ member: Region[] }> {
    return client
      .get<{ member: Region[] }>('/api/v1/regions')
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
    categoryId?: number
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

  createCategory(data: { name: string; description?: string; icon?: string }): Promise<ServiceCategory> {
    return client
      .post<ServiceCategory>('/api/v1/service_categories', data)
      .then(r => r.data)
  },

  updateCategory(id: number, data: { name?: string; description?: string; icon?: string }): Promise<ServiceCategory> {
    return client
      .patch<ServiceCategory>(`/api/v1/service_categories/${id}`, data)
      .then(r => r.data)
  },

  deleteCategory(id: number): Promise<void> {
    return client
      .delete(`/api/v1/service_categories/${id}`)
      .then(() => undefined)
  },
}
