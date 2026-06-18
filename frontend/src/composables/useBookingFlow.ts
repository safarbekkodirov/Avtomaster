import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingStore } from '@/stores/booking.store'

export function useBookingFlow(masterId: number) {
    const router       = useRouter()
    const bookingStore = useBookingStore()

    const selectedSlot    = ref<{ date: string; startTime: string; endTime: string } | null>(null)
    const selectedService = ref<number | null>(null)
    const notes           = ref('')
    const submitting      = ref(false)
    const error           = ref<string | null>(null)

    function selectSlot(slot: { date: string; startTime: string; endTime: string }): void {
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
                serviceId: selectedService.value,
                slotDate: selectedSlot.value.date,
                slotStartTime: selectedSlot.value.startTime,
                slotEndTime: selectedSlot.value.endTime,
                notes: notes.value || undefined,
            })

            await router.push({ name: 'booking', params: { id: booking.id } })
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
