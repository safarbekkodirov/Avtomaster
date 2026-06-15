// src/composables/useBookingFlow.ts
// Оркестрирует: выбор слота → создание бронирования → редирект на оплату

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingStore } from '@/stores/booking.store'
import type { TimeSlot } from '@/types/master.types'

export function useBookingFlow(masterId: number) {
    const router       = useRouter()
    const bookingStore = useBookingStore()

    const selectedSlot    = ref<(TimeSlot & { date: string }) | null>(null)
    const selectedService = ref<number | null>(null)
    const notes           = ref('')
    const submitting      = ref(false)
    const error           = ref<string | null>(null)

    function selectSlot(slot: TimeSlot & { date: string }): void {
        selectedSlot.value = slot
    }

    function selectService(serviceId: number): void {
        selectedService.value = serviceId
    }

    async function submit(): Promise<void> {
        if (!selectedSlot.value || !selectedService.value) {
            error.value = 'Выберите услугу и время'
            return
        }

        submitting.value = true
        error.value      = null

        try {
            const booking = await bookingStore.create({
                masterId,
                slotId:    selectedSlot.value.id,
                serviceId: selectedService.value,
                notes:     notes.value || undefined,
            })

            // Редирект на оплату (Payment модуль)
            await router.push({ name: 'payment', params: { bookingId: booking.id } })
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Ошибка при создании записи'
        } finally {
            submitting.value = false
        }
    }

    return {
        selectedSlot,
        selectedService,
        notes,
        submitting,
        error,
        selectSlot,
        selectService,
        submit,
    }
}