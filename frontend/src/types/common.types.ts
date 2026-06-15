export interface Pagination {
  page:       number
  perPage:    number
  total:      number
  totalPages: number
}

export interface PaginatedResponse<T> {
  data:       T[]
  pagination: Pagination
}
