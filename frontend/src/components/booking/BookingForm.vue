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
                <span v-if="service.category" class="booking-form__service-category">{{ service.category.name }}</span>
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
            ✅ {{ selectedSlot.date }} · {{ selectedSlot.startTime }} — {{ selectedSlot.endTime }}
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
.booking-form { display: flex; flex-direction: column; gap: 1rem; }

.booking-form__services { border: none; padding: 0; margin: 0; }
.booking-form__services legend {
    font-weight: 700; font-size: 0.85rem;
    color: rgba(255,255,255,0.5); text-transform: uppercase;
    letter-spacing: 0.5px; margin-bottom: 8px;
}
.booking-form__service {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    border: 1.5px solid rgba(255,255,255,0.08); border-radius: 12px;
    cursor: pointer; transition: all 0.2s;
}
.booking-form__service:hover { border-color: rgba(255,255,255,0.15); }
.booking-form__service--selected {
    border-color: rgba(230,57,70,0.5);
    background: rgba(230,57,70,0.08);
}
.booking-form__service input[type="radio"] { accent-color: #e63946; }
.booking-form__service-name { font-weight: 600; flex: 1; color: white; font-size: 0.95rem; }
.booking-form__service-category {
    color: rgba(255,255,255,0.4); font-size: 0.8rem; padding: 2px 8px;
    background: rgba(255,255,255,0.06); border-radius: 6px;
}
.booking-form__service-meta { color: rgba(255,255,255,0.4); font-size: 0.85rem; }

.booking-form__selected {
    padding: 12px 16px; background: rgba(74,222,128,0.1);
    border: 1px solid rgba(74,222,128,0.2); border-radius: 10px;
    font-weight: 600; color: #4ade80; font-size: 0.95rem;
}

.booking-form__notes { display: block; }
.booking-form__notes span {
    display: block; font-size: 0.8rem; font-weight: 600;
    color: rgba(255,255,255,0.4); margin-bottom: 6px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.booking-form__notes textarea {
    width: 100%; padding: 12px;
    background: rgba(255,255,255,0.06); border: 1.5px solid rgba(255,255,255,0.1);
    border-radius: 10px; font-size: 0.9rem; color: white;
    resize: vertical; font-family: inherit; outline: none;
    transition: all 0.2s;
}
.booking-form__notes textarea::placeholder { color: rgba(255,255,255,0.25); }
.booking-form__notes textarea:focus {
    border-color: rgba(230,57,70,0.5);
    box-shadow: 0 0 0 3px rgba(230,57,70,0.1);
}

.booking-form__error { color: #ff6b6b; font-size: 0.9rem; margin: 0; }

.booking-form__submit {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #e63946, #d32f3f); color: white;
    border: none; border-radius: 12px; font-size: 1rem; font-weight: 700;
    cursor: pointer; transition: all 0.25s;
}
.booking-form__submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}
.booking-form__submit:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
