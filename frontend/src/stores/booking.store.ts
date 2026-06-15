// src/stores/booking.store.ts

import { defineStore } from 'pinia'
import { ref } from 'vue'
import { bookingsApi } from '@/api/bookings'
import type {
  Booking,
  BookingListParams,
  BookingStatus,
  CancelBookingPayload,
  CreateBookingPayload,
} from '@/types/booking.types'
import type { Pagination } from '@/types/common.types'

export const useBookingStore = defineStore('booking', () => {
  const bookings   = ref<Booking[]>([])
  const current    = ref<Booking | null>(null)
  const pagination = ref<Pagination | null>(null)
  const loading    = ref(false)
  const error      = ref<string | null>(null)

  async function create(payload: CreateBookingPayload): Promise<Booking> {
    loading.value = true
    error.value   = null
    try {
      const result  = await bookingsApi.create(payload)
      bookings.value.unshift(result.data)
      return result.data
    } catch (e) {
      error.value = extractMessage(e)
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchList(params?: BookingListParams): Promise<void> {
    loading.value = true
    try {
      const result     = await bookingsApi.list(params)
      bookings.value   = result.data
      pagination.value = result.pagination
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id: number): Promise<void> {
    const result  = await bookingsApi.get(id)
    current.value = result.data
  }

  async function confirm(id: number): Promise<void> {
    await updateStatus(id, () => bookingsApi.confirm(id))
  }

  async function complete(id: number): Promise<void> {
    await updateStatus(id, () => bookingsApi.complete(id))
  }

  async function cancel(id: number, payload?: CancelBookingPayload): Promise<void> {
    await updateStatus(id, () => bookingsApi.cancel(id, payload))
  }

  async function updateStatus(
      id: number,
      action: () => Promise<{ data: Booking }>
  ): Promise<void> {
    const result  = await action()
    // Обновляем в списке если есть
    const idx = bookings.value.findIndex(b => b.id === id)
    if (idx !== -1) bookings.value[idx] = result.data
    // Обновляем current если открыт
    if (current.value?.id === id) current.value = result.data
  }

  return {
    bookings,
    current,
    pagination,
    loading,
    error,
    create,
    fetchList,
    fetchOne,
    confirm,
    complete,
    cancel,
  }
})

function extractMessage(e: unknown): string {
  if (e instanceof Error) return e.message
  return 'Неизвестная ошибка'
}