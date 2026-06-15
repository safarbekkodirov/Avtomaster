// src/api/bookings.ts

import client from './client'
import type {
  Booking,
  BookingListParams,
  CancelBookingPayload,
  CreateBookingPayload,
} from '@/types/booking.types'
import type { PaginatedResponse } from '@/types/common.types'

export const bookingsApi = {
  create(payload: CreateBookingPayload): Promise<{ data: Booking }> {
    return client.post<{ data: Booking }>('/api/v1/bookings', payload).then(r => r.data)
  },

  list(params?: BookingListParams): Promise<PaginatedResponse<Booking>> {
    return client.get<PaginatedResponse<Booking>>('/api/v1/bookings', { params }).then(r => r.data)
  },

  get(id: number): Promise<{ data: Booking }> {
    return client.get<{ data: Booking }>(`/api/v1/bookings/${id}`).then(r => r.data)
  },

  confirm(id: number): Promise<{ data: Booking }> {
    return client.patch<{ data: Booking }>(`/api/v1/bookings/${id}/confirm`).then(r => r.data)
  },

  complete(id: number): Promise<{ data: Booking }> {
    return client.patch<{ data: Booking }>(`/api/v1/bookings/${id}/complete`).then(r => r.data)
  },

  cancel(id: number, payload?: CancelBookingPayload): Promise<{ data: Booking }> {
    return client.patch<{ data: Booking }>(`/api/v1/bookings/${id}/cancel`, payload).then(r => r.data)
  },
}