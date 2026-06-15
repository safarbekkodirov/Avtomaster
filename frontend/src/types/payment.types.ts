// src/types/payment.types.ts

export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded'

export interface Payment {
  id:          number
  bookingId:   number
  status:      PaymentStatus
  amount:      string
  currency:    string
  checkoutUrl: string | null
  createdAt:   string
}