// src/api/reviews.ts

import client from './client'
import type { CreateReviewPayload, Review } from '@/types/review.types'
import type { PaginatedResponse } from '@/types/common.types'

export const reviewsApi = {
  create(bookingId: number, payload: CreateReviewPayload): Promise<{ data: Review }> {
    return client
        .post<{ data: Review }>(`/api/v1/bookings/${bookingId}/review`, payload)
        .then(r => r.data)
  },

  getMasterReviews(
      masterId: number,
      params?: { page?: number; perPage?: number }
  ): Promise<PaginatedResponse<Review>> {
    return client
        .get<PaginatedResponse<Review>>(`/api/v1/masters/${masterId}/reviews`, { params })
        .then(r => r.data)
  },
}