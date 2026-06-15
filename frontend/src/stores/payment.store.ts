// src/stores/payment.store.ts

import { defineStore } from 'pinia'
import { ref } from 'vue'
import { paymentsApi } from '@/api/payments'
import type { Payment } from '@/types/payment.types'

export const usePaymentStore = defineStore('payment', () => {
    const current  = ref<Payment | null>(null)
    const loading  = ref(false)
    const error    = ref<string | null>(null)

    async function initiate(bookingId: number): Promise<string> {
        loading.value = true
        error.value   = null
        try {
            const result  = await paymentsApi.initiate(bookingId)
            current.value = result.data

            if (!result.data.checkoutUrl) {
                throw new Error('Checkout URL не получен от сервера')
            }

            return result.data.checkoutUrl
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Ошибка инициализации оплаты'
            throw e
        } finally {
            loading.value = false
        }
    }

    return { current, loading, error, initiate }
})