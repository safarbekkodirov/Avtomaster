export type NotificationType =
  | 'booking_created'
  | 'booking_confirmed'
  | 'booking_completed'
  | 'booking_cancelled'
  | 'payment_received'
  | 'review_received'

export interface Notification {
  id:          number
  type:        NotificationType
  title:       string
  body:        string
  relatedId:   number | null
  relatedType: string | null
  isRead:      boolean
  createdAt:   string
}

export interface UnreadCountResponse {
  count: number
}
