import client from './client'

export interface AdminUser {
  id: number
  email: string
  firstName: string | null
  lastName: string | null
  roles: string[]
  createdAt: string
}

export interface AdminMasterService {
  id: number
  name: string
  price: number
  durationMinutes: number
  categoryName: string | null
}

export interface AdminMaster {
  id: number
  firstName: string
  lastName: string
  phone: string | null
  bio: string | null
  regionName: string
  address: string | null
  rating: string
  reviewsCount: number
  isVerified: boolean
  services: AdminMasterService[]
  createdAt: string
}

export interface CreateMasterPayload {
  email: string
  password: string
  firstName: string
  lastName: string
  phone?: string
  regionName: string
  address?: string
}

export interface UpdateMasterPayload {
  firstName?: string
  lastName?: string
  phone?: string
  bio?: string
  regionName?: string
  address?: string
}

export const adminApi = {
  getUsers(): Promise<{ member: AdminUser[] }> {
    return client
      .get<{ member: AdminUser[] }>('/api/v1/users')
      .then(r => r.data)
  },

  deleteUser(id: number): Promise<void> {
    return client
      .delete(`/api/v1/users/${id}`)
      .then(() => undefined)
  },

  getMasters(): Promise<{ member: AdminMaster[] }> {
    return client
      .get<{ member: AdminMaster[] }>('/api/v1/masters')
      .then(r => r.data)
  },

  createMaster(payload: CreateMasterPayload): Promise<void> {
    return client
      .post('/api/v1/users', {
        email: payload.email,
        password: payload.password,
        firstName: payload.firstName,
        lastName: payload.lastName,
        roles: ['ROLE_MASTER'],
        phone: payload.phone || undefined,
        regionName: payload.regionName,
        address: payload.address || undefined,
      })
      .then(() => undefined)
  },

  deleteMaster(id: number): Promise<void> {
    return client
      .delete(`/api/v1/masters/${id}`)
      .then(() => undefined)
  },

  updateMaster(id: number, payload: UpdateMasterPayload): Promise<void> {
    return client
      .patch(`/api/v1/masters/${id}`, payload)
      .then(() => undefined)
  },

  getRegions(): Promise<{ member: { id: number; name: string }[] }> {
    return client
      .get('/api/v1/regions')
      .then(r => r.data)
  },
}
