// src/api/payments.ts

import client from './client'
import type { Payment } from '@/types/payment.types'

export const paymentsApi = {
  initiate(bookingId: number): Promise<{ data: Payment }> {
    return client
        .post<{ data: Payment }>(`/api/v1/bookings/${bookingId}/payment`)
        .then(r => r.data)
  },
}