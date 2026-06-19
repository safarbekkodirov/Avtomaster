import client from './client'
import type {
  Booking,
  BookingListParams,
  CancelBookingPayload,
  CreateBookingPayload,
  BookingStatus,
} from '@/types/booking.types'

const PATCH_HEADERS = { 'Content-Type': 'application/merge-patch+json' }

export const bookingsApi = {
  async create(payload: CreateBookingPayload): Promise<Booking> {
    const r = await client.post('/api/v1/bookings', payload)
    return r.data as any
  },

  async list(params?: BookingListParams): Promise<{ data: Booking[]; pagination: any }> {
    const r = await client.get('/api/v1/bookings', { params })
    const body = r.data as any
    return {
      data: body.member ?? body.data ?? [],
      pagination: body.pagination ?? { page: 1, totalPages: 1, total: 0 },
    }
  },

  async get(id: number): Promise<Booking> {
    const r = await client.get(`/api/v1/bookings/${id}`)
    return r.data as any
  },

  async confirm(id: number): Promise<Booking> {
    const r = await client.patch(`/api/v1/bookings/${id}/confirm`, {}, { headers: PATCH_HEADERS })
    return r.data as any
  },

  async complete(id: number): Promise<Booking> {
    const r = await client.patch(`/api/v1/bookings/${id}/complete`, {}, { headers: PATCH_HEADERS })
    return r.data as any
  },

  async cancel(id: number, payload?: CancelBookingPayload): Promise<Booking> {
    const r = await client.patch(`/api/v1/bookings/${id}/cancel`, payload ?? {}, { headers: PATCH_HEADERS })
    return r.data as any
  },

  async listMaster(status?: BookingStatus): Promise<{ data: Booking[] }> {
    const params: Record<string, string> = {}
    if (status) params.status = status
    const r = await client.get('/api/v1/bookings/master', { params })
    return r.data as any
  },
}
