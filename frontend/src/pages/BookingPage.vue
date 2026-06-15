<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBookingStore } from '@/stores/booking.store'
import ReviewForm from '@/components/review/ReviewForm.vue'

const route  = useRoute()
const router = useRouter()
const store  = useBookingStore()

const bookingId = Number(route.params.id)
const showReview = ref(false)
const reviewSubmitted = ref(false)

const statusLabels: Record<string, string> = {
  pending:   'Ожидает подтверждения',
  confirmed: 'Подтверждено',
  completed: 'Завершено',
  cancelled: 'Отменено',
  refunded:  'Возврат средств',
}

const statusColors: Record<string, string> = {
  pending:   '#fff3cd',
  confirmed: '#cce5ff',
  completed: '#d4edda',
  cancelled: '#f8d7da',
  refunded:  '#e2e3e5',
}

onMounted(() => store.fetchOne(bookingId))

async function cancelBooking(): Promise<void> {
  if (!confirm('Вы уверены, что хотите отменить запись?')) return
  await store.cancel(bookingId)
}

function onReviewSubmitted(): void {
  reviewSubmitted.value = true
  showReview.value = false
}
</script>

<template>
  <div class="booking-page">
    <div v-if="store.loading" class="booking-page__loading">Загрузка...</div>

    <div v-else-if="!store.current" class="booking-page__error">
      <p>Бронирование не найдено</p>
      <RouterLink to="/dashboard">Вернуться в кабинет</RouterLink>
    </div>

    <template v-else>
      <div class="booking-card">
        <div class="booking-card__header">
          <h1>Запись #{{ store.current.id }}</h1>
          <span
            class="booking-card__status"
            :style="{ background: statusColors[store.current.status] || '#eee' }"
          >
            {{ statusLabels[store.current.status] || store.current.status }}
          </span>
        </div>

        <div class="booking-card__details">
          <div class="booking-card__row">
            <span class="booking-card__label">Мастер</span>
            <span class="booking-card__value">{{ store.current.masterFirstName }} {{ store.current.masterLastName }}</span>
          </div>
          <div class="booking-card__row">
            <span class="booking-card__label">Услуга</span>
            <span class="booking-card__value">{{ store.current.serviceName }} ({{ store.current.serviceDuration }} мин)</span>
          </div>
          <div class="booking-card__row">
            <span class="booking-card__label">Дата</span>
            <span class="booking-card__value">{{ store.current.slotDate }}</span>
          </div>
          <div class="booking-card__row">
            <span class="booking-card__label">Время</span>
            <span class="booking-card__value">{{ store.current.slotStartTime }} — {{ store.current.slotEndTime }}</span>
          </div>
          <div class="booking-card__row">
            <span class="booking-card__label">Стоимость</span>
            <span class="booking-card__value booking-card__price">{{ store.current.total }} ₽</span>
          </div>
          <div v-if="store.current.notes" class="booking-card__row">
            <span class="booking-card__label">Примечание</span>
            <span class="booking-card__value">{{ store.current.notes }}</span>
          </div>
          <div v-if="store.current.cancelledReason" class="booking-card__row">
            <span class="booking-card__label">Причина отмены</span>
            <span class="booking-card__value booking-card__cancel-reason">{{ store.current.cancelledReason }}</span>
          </div>
        </div>

        <div class="booking-card__actions">
          <button
            v-if="store.current.status === 'pending' || store.current.status === 'confirmed'"
            class="btn btn--danger"
            @click="cancelBooking"
          >
            Отменить запись
          </button>

          <button
            v-if="store.current.status === 'completed' && !reviewSubmitted && !showReview"
            class="btn btn--primary"
            @click="showReview = true"
          >
            Оставить отзыв
          </button>

          <RouterLink v-if="store.current.status === 'completed'" to="/search" class="btn btn--outline">
            Найти другого мастера
          </RouterLink>
        </div>
      </div>

      <div v-if="showReview" class="booking-review">
        <ReviewForm :booking-id="bookingId" @submitted="onReviewSubmitted" />
      </div>

      <p v-if="reviewSubmitted" class="review-thanks">
        Спасибо за ваш отзыв!
      </p>
    </template>
  </div>
</template>

<style scoped>
.booking-page { max-width: 700px; margin: 0 auto; padding: 2rem 1rem; }

.booking-page__loading,
.booking-page__error { text-align: center; padding: 4rem 1rem; color: #666; }
.booking-page__error a { color: #e63946; }

.booking-card {
    background: white; border: 1px solid #e5e5e5; border-radius: 12px;
    overflow: hidden;
}
.booking-card__header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.5rem; border-bottom: 1px solid #eee;
}
.booking-card__header h1 { margin: 0; font-size: 1.3rem; }
.booking-card__status { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }

.booking-card__details { padding: 1.5rem; }
.booking-card__row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
.booking-card__row:last-child { border-bottom: none; }
.booking-card__label { color: #666; font-size: 0.9rem; }
.booking-card__value { font-weight: 600; text-align: right; }
.booking-card__price { color: #e63946; font-size: 1.1rem; }
.booking-card__cancel-reason { color: #dc3545; font-weight: 400; }

.booking-card__actions {
    display: flex; gap: 8px; padding: 0 1.5rem 1.5rem; flex-wrap: wrap;
}

.btn {
    padding: 10px 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 600;
    cursor: pointer; text-decoration: none; border: none; transition: all 0.2s;
}
.btn--primary { background: #e63946; color: white; }
.btn--primary:hover { background: #c1121f; }
.btn--danger { background: white; color: #dc3545; border: 1px solid #dc3545; }
.btn--danger:hover { background: #dc3545; color: white; }
.btn--outline { background: white; color: #1a1a2e; border: 1px solid #ddd; }
.btn--outline:hover { border-color: #1a1a2e; }

.booking-review { margin-top: 1.5rem; }
.review-thanks { text-align: center; color: #28a745; font-weight: 600; padding: 1rem; }
</style>
