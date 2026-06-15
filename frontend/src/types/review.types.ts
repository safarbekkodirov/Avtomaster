export interface Review {
  id:              number
  bookingId:       number
  clientFirstName: string
  clientLastName:  string
  clientAvatar:    string | null
  rating:          number
  comment:         string | null
  createdAt:       string
}

export interface CreateReviewPayload {
  rating:   number
  comment?: string
}
