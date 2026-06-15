export interface MasterService {
  id:              number
  name:            string
  price:           number
  durationMinutes: number
  categoryName:    string
}

export interface Master {
  id:           number
  firstName:    string
  lastName:     string
  avatar:       string | null
  bio:          string | null
  regionName:   string
  address:      string | null
  rating:       number
  reviewsCount: number
  isVerified:   boolean
  distanceKm:   number | null
  services:     MasterService[]
}

export interface MasterSearchResult {
  data:       Master[]
  pagination: Pagination
}

export interface MasterSearchParams {
  regionSlug?:  string
  lat?:         number
  lng?:         number
  radiusKm?:    number
  categoryId?:  number
  minRating?:   number
  maxPrice?:    number
  sortBy?:      'rating' | 'distance' | 'price'
  page?:        number
  perPage?:     number
}

export interface TimeSlot {
  id:        number
  startTime: string
  endTime:   string
}

export interface Pagination {
  page:       number
  perPage:    number
  total:      number
  totalPages: number
}

export type SlotsByDate = Record<string, TimeSlot[]>
