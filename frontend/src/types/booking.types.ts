export type BookingStatus =
  | 'pending'
  | 'confirmed'
  | 'completed'
  | 'cancelled'
  | 'refunded'

export interface Booking {
  id:              number
  status:          BookingStatus
  total:           string
  serviceName:     string
  serviceDuration: number
  masterFirstName: string
  masterLastName:  string
  slotDate:        string
  slotStartTime:   string
  slotEndTime:     string
  notes:           string | null
  cancelledReason: string | null
  createdAt:       string
}

export interface CreateBookingPayload {
  masterId:  number
  slotId:    number
  serviceId: number
  notes?:    string
}

export interface CancelBookingPayload {
  reason?: string
}

export interface BookingListParams {
  page?:    number
  perPage?: number
  status?:  BookingStatus
}
