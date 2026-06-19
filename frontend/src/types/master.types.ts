export interface Region {
  id:   number
  name: string
  slug: string
  lat:  number | null
  lng:  number | null
}

export interface ServiceCategory {
  id:          number
  name:        string
  slug:        string
  description: string | null
  icon:        string | null
}

export interface MasterService {
  id:              number
  name:            string
  price:           number
  durationMinutes: number
  categoryName:    string | null
  category:        ServiceCategory | null
}

export interface Master {
  id:           number
  firstName:    string
  lastName:     string
  phone:        string | null
  bio:          string | null
  regionName:   string
  address:      string | null
  lat:          number | null
  lng:          number | null
  rating:       number
  reviewsCount: number
  isVerified:   boolean
  services:     MasterService[]
  distanceKm:   number | null
}

export interface MasterSearchResult {
  data:       Master[]
  pagination: Pagination
}

export interface MasterSearchParams {
  regionName?: string
  categorySlug?: string
  lat?:         number
  lng?:         number
  radiusKm?:    number
  minRating?:   number
  maxPrice?:    number
  sortBy?:      'rating' | 'distance' | 'price'
  page?:        number
  perPage?:     number
}

export interface CreateMasterPayload {
  firstName:  string
  lastName:   string
  phone?:     string
  bio?:       string
  regionName: string
  address?:   string
  lat?:       number
  lng?:       number
}

export interface UpdateMasterPayload {
  firstName?: string
  lastName?:  string
  phone?:     string
  bio?:       string
  regionName?: string
  address?:   string
  lat?:       number
  lng?:       number
}

export interface Pagination {
  page:       number
  perPage:    number
  total:      number
  totalPages: number
}
