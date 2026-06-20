<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import MasterSlotPicker from '@/components/master/MasterSlotPicker.vue'
import type { Master } from '@/types/master.types'
import client from '@/api/client'

const props = defineProps<{ master: Master }>()
const router = useRouter()

const name       = ref('')
const phone      = ref('')
const selectedService = ref<number | null>(null)
const selectedSlot    = ref<any>(null)
const notes           = ref('')
const submitting      = ref(false)
const error           = ref('')
const success         = ref(false)

const canSubmit = computed(() =>
  name.value.trim() !== '' &&
  phone.value.trim() !== '' &&
  selectedService.value !== null &&
  selectedSlot.value !== null &&
  !submitting.value
)

function selectService(id: number) {
  selectedService.value = id
}

function selectSlot(slot: any) {
  selectedSlot.value = slot
}

async function submit() {
  if (!canSubmit.value) return
  submitting.value = true
  error.value = ''

  try {
    await client.post('/api/v1/bookings/quick', {
      name: name.value.trim(),
      phone: phone.value.trim(),
      masterId: props.master.id,
      serviceId: selectedService.value,
      slotDate: selectedSlot.value.date,
      slotStartTime: selectedSlot.value.startTime,
      slotEndTime: selectedSlot.value.endTime,
      notes: notes.value || undefined,
    })
    success.value = true
  } catch (e: any) {
    error.value = e?.response?.data?.detail || 'Ошибка при записи'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="quick-form">
    <!-- Success -->
    <div v-if="success" class="quick-form__success">
      <div class="success-icon">✅</div>
      <h3>Запись создана!</h3>
      <p>Мастер свяжется с вами для подтверждения</p>
      <button class="btn-primary" @click="router.push('/')">На главную</button>
    </div>

    <!-- Form -->
    <template v-else>
      <!-- Контакты -->
      <div class="quick-form__section">
        <h3 class="quick-form__label">Ваши данные</h3>
        <div class="quick-form__fields">
          <div class="field">
            <label>Имя *</label>
            <input v-model="name" type="text" placeholder="Как вас зовут?" />
          </div>
          <div class="field">
            <label>Телефон *</label>
            <input v-model="phone" type="tel" placeholder="+996..." />
          </div>
        </div>
      </div>

      <!-- Услуга -->
      <div class="quick-form__section">
        <h3 class="quick-form__label">Услуга</h3>
        <div class="quick-form__services">
          <label
            v-for="service in master.services"
            :key="service.id"
            class="service-option"
            :class="{ 'service-option--selected': selectedService === service.id }"
          >
            <input
              type="radio"
              name="service"
              :value="service.id"
              @change="selectService(service.id)"
            />
            <span class="service-option__name">{{ service.name }}</span>
            <span class="service-option__price">{{ service.price.toLocaleString('ru-RU') }} ₽</span>
          </label>
        </div>
      </div>

      <!-- Время -->
      <div class="quick-form__section">
        <h3 class="quick-form__label">Время</h3>
        <MasterSlotPicker
          :master-id="master.id"
          :service-id="selectedService ?? undefined"
          @select="selectSlot"
        />
        <div v-if="selectedSlot" class="selected-time">
          ✅ {{ selectedSlot.date }} · {{ selectedSlot.startTime }} — {{ selectedSlot.endTime }}
        </div>
      </div>

      <!-- Примечание -->
      <div class="quick-form__section">
        <label class="quick-form__label">Примечание (необязательно)</label>
        <textarea v-model="notes" rows="2" placeholder="Опишите проблему..." />
      </div>

      <!-- Ошибка -->
      <p v-if="error" class="quick-form__error">{{ error }}</p>

      <!-- Кнопка -->
      <button
        class="quick-form__submit"
        :disabled="!canSubmit"
        @click="submit"
      >
        {{ submitting ? 'Отправка...' : 'Записаться' }}
      </button>

      <p class="quick-form__hint">
        Нажимая "Записаться", вы соглашаетесь с условиями сервиса
      </p>
    </template>
  </div>
</template>

<style scoped>
.quick-form { }

.quick-form__success {
  text-align: center; padding: 2rem 0;
}
.success-icon { font-size: 3rem; margin-bottom: 1rem; }
.quick-form__success h3 { color: white; margin: 0 0 0.5rem; font-size: 1.3rem; }
.quick-form__success p { color: rgba(255,255,255,0.5); margin: 0 0 1.5rem; }

.quick-form__section {
  margin-bottom: 1.5rem;
}
.quick-form__label {
  margin: 0 0 0.75rem; font-size: 0.85rem; font-weight: 700;
  color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.5px;
}

.quick-form__fields {
  display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;
}
.field label {
  display: block; font-size: 0.8rem; font-weight: 600;
  color: rgba(255,255,255,0.4); margin-bottom: 4px;
}
.field input {
  width: 100%; padding: 12px 14px;
  background: rgba(255,255,255,0.06); border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 10px; font-size: 0.95rem; color: white; outline: none;
  transition: all 0.2s;
}
.field input::placeholder { color: rgba(255,255,255,0.25); }
.field input:focus {
  border-color: rgba(230,57,70,0.5);
  box-shadow: 0 0 0 3px rgba(230,57,70,0.1);
}

.quick-form__services {
  display: flex; flex-direction: column; gap: 0.5rem;
}
.service-option {
  display: flex; align-items: center; gap: 12px; padding: 12px 16px;
  background: rgba(255,255,255,0.04); border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 10px; cursor: pointer; transition: all 0.2s;
}
.service-option:hover { border-color: rgba(255,255,255,0.15); }
.service-option--selected {
  border-color: rgba(230,57,70,0.5); background: rgba(230,57,70,0.08);
}
.service-option input[type="radio"] { accent-color: #e63946; }
.service-option__name { flex: 1; font-weight: 600; color: white; font-size: 0.95rem; }
.service-option__price { color: #e63946; font-weight: 700; font-size: 0.95rem; }

.selected-time {
  margin-top: 0.75rem; padding: 10px 14px;
  background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.2);
  border-radius: 8px; color: #4ade80; font-weight: 600; font-size: 0.9rem;
}

textarea {
  width: 100%; padding: 12px 14px;
  background: rgba(255,255,255,0.06); border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 10px; font-size: 0.95rem; color: white; outline: none;
  resize: vertical; font-family: inherit; transition: all 0.2s;
}
textarea::placeholder { color: rgba(255,255,255,0.25); }
textarea:focus {
  border-color: rgba(230,57,70,0.5);
  box-shadow: 0 0 0 3px rgba(230,57,70,0.1);
}

.quick-form__error {
  color: #ff6b6b; font-size: 0.9rem; margin: 0 0 1rem;
  background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.2);
  padding: 10px 14px; border-radius: 8px;
}

.quick-form__submit {
  width: 100%; padding: 14px;
  background: linear-gradient(135deg, #e63946, #d32f3f); color: white;
  border: none; border-radius: 12px; font-size: 1rem; font-weight: 700;
  cursor: pointer; transition: all 0.25s;
}
.quick-form__submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}
.quick-form__submit:disabled { opacity: 0.5; cursor: not-allowed; }

.quick-form__hint {
  text-align: center; margin: 0.75rem 0 0;
  font-size: 0.8rem; color: rgba(255,255,255,0.25);
}

@media (max-width: 480px) {
  .quick-form__fields { grid-template-columns: 1fr; }
}
</style>
