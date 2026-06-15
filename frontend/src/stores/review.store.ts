// src/stores/review.store.ts

import { defineStore } from 'pinia'
import { ref } from 'vue'
import { reviewsApi } from '@/api/reviews'
import type { CreateReviewPayload, Review } from '@/types/review.types'
import type { Pagination } from '@/types/common.types'

export const useReviewStore = defineStore('review', () => {
    const reviews    = ref<Review[]>([])
    const pagination = ref<Pagination | null>(null)
    const loading    = ref(false)
    const error      = ref<string | null>(null)

    async function create(bookingId: number, payload: CreateReviewPayload): Promise<Review> {
        loading.value = true
        error.value   = null
        try {
            const result = await reviewsApi.create(bookingId, payload)
            reviews.value.unshift(result.data)
            return result.data
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Ошибка при создании отзыва'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function fetchMasterReviews(
        masterId: number,
        page    = 1,
        perPage = 10,
    ): Promise<void> {
        loading.value = true
        try {
            const result     = await reviewsApi.getMasterReviews(masterId, { page, perPage })
            reviews.value    = page === 1 ? result.data : [...reviews.value, ...result.data]
            pagination.value = result.pagination
        } finally {
            loading.value = false
        }
    }

    return { reviews, pagination, loading, error, create, fetchMasterReviews }
})