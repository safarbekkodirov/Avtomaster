<!-- src/components/booking/BookingForm.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import { useBookingFlow } from '@/composables/useBookingFlow'
import MasterSlotPicker from '@/components/master/MasterSlotPicker.vue'
import type { Master } from '@/types/master.types'

const props = defineProps<{ master: Master }>()

const {
    selectedSlot,
    selectedService,
    notes,
    submitting,
    error,
    selectSlot,
    selectService,
    submit,
} = useBookingFlow(props.master.id)

const canSubmit = computed(
    () => selectedSlot.value !== null && selectedService.value !== null && !submitting.value
)
</script>

<template>
    <div class="booking-form">
        <h2 class="booking-form__title">Запись к мастеру</h2>

        <!-- Выбор услуги -->
        <fieldset class="booking-form__services">
            <legend>Выберите услугу</legend>
            <label
                v-for="service in master.services"
                :key="service.id"
                class="booking-form__service"
                :class="{ 'booking-form__service--selected': selectedService === service.id }"
            >
                <input
                    type="radio"
                    name="service"
                    :value="service.id"
                    :checked="selectedService === service.id"
                    @change="selectService(service.id)"
                />
                <span class="booking-form__service-name">{{ service.name }}</span>
                <span class="booking-form__service-meta">
          {{ service.durationMinutes }} мин · {{ service.price.toLocaleString('ru-RU') }} ₽
        </span>
            </label>
        </fieldset>

        <!-- Выбор слота -->
        <MasterSlotPicker
            :master-id="master.id"
            :service-id="selectedService ?? undefined"
            @select="selectSlot"
        />

        <!-- Выбранное время -->
        <div v-if="selectedSlot" class="booking-form__selected">
            <span>{{ selectedSlot.date }} · {{ selectedSlot.startTime }} — {{ selectedSlot.endTime }}</span>
        </div>

        <!-- Примечание -->
        <label class="booking-form__notes">
            <span>Примечание (необязательно)</span>
            <textarea
                v-model="notes"
                maxlength="500"
                rows="3"
                placeholder="Опишите проблему или пожелания..."
            />
        </label>

        <!-- Ошибка -->
        <p v-if="error" class="booking-form__error" role="alert">{{ error }}</p>

        <button
            type="button"
            class="booking-form__submit"
            :disabled="!canSubmit"
            :aria-busy="submitting"
            @click="submit"
        >
            {{ submitting ? 'Создание записи...' : 'Записаться' }}
        </button>
    </div>
</template>

<style scoped>
.booking-form { background: white; padding: 2rem; border-radius: 12px; border: 1px solid #e5e5e5; }
.booking-form__title { margin: 0 0 1.5rem; font-size: 1.3rem; }

.booking-form__services { border: none; padding: 0; margin: 0 0 1.5rem; }
.booking-form__services legend { font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }
.booking-form__service {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    border: 2px solid #e5e5e5; border-radius: 8px; margin-bottom: 8px;
    cursor: pointer; transition: all 0.2s;
}
.booking-form__service:hover { border-color: #ccc; }
.booking-form__service--selected { border-color: #e63946; background: #fef2f2; }
.booking-form__service input[type="radio"] { accent-color: #e63946; }
.booking-form__service-name { font-weight: 600; flex: 1; }
.booking-form__service-meta { color: #666; font-size: 0.85rem; }

.booking-form__selected {
    padding: 12px 16px; background: #e8f5e9; border-radius: 8px;
    margin: 1rem 0; font-weight: 600; color: #2e7d32;
}

.booking-form__notes { display: block; margin: 1rem 0; }
.booking-form__notes span { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
.booking-form__notes textarea {
    width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;
    font-size: 0.9rem; resize: vertical; font-family: inherit;
}

.booking-form__error { color: #e63946; font-size: 0.9rem; margin: 0.5rem 0; }

.booking-form__submit {
    width: 100%; padding: 14px; background: #e63946; color: white;
    border: none; border-radius: 8px; font-size: 1rem; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
}
.booking-form__submit:hover:not(:disabled) { background: #c1121f; }
.booking-form__submit:disabled { opacity: 0.5; cursor: wait; }
</style>