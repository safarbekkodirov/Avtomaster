<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useBookingStore } from '@/stores/booking.store'
import { useRouter } from 'vue-router'

const auth    = useAuthStore()
const booking = useBookingStore()
const router  = useRouter()

onMounted(() => booking.fetchList())

async function logout() {
  await auth.logout()
  router.push({ name: 'login' })
}

const statusLabels: Record<string, string> = {
  pending: 'Ожидает',
  confirmed: 'Подтверждена',
  completed: 'Завершена',
  cancelled: 'Отменена',
}
</script>

<template>
  <div class="page">
    <header class="page-header">
      <div class="page-header__left">
        <h1>Мои записи</h1>
      </div>
      <div class="page-header__right">
        <span class="user-name">{{ auth.user?.firstName }} {{ auth.user?.lastName }}</span>
        <RouterLink to="/notifications" class="btn-ghost">Уведомления</RouterLink>
        <RouterLink v-if="auth.isMaster" to="/master-dashboard" class="btn-outline">Кабинет мастера</RouterLink>
        <button class="btn-ghost" @click="logout">Выйти</button>
      </div>
    </header>

    <div v-if="booking.loading" class="loading">Загрузка...</div>

    <div v-else-if="!booking.bookings.length" class="empty-state">
      <p>У вас пока нет записей</p>
      <RouterLink to="/search" class="btn-primary">Найти мастера</RouterLink>
    </div>

    <div v-else class="bookings-list">
      <div v-for="b in booking.bookings" :key="b.id" class="booking-card">
        <div class="booking-card__header">
          <h3 class="booking-card__service">{{ b.serviceName }}</h3>
          <span class="status-badge" :class="`status-badge--${b.status}`">
            {{ statusLabels[b.status] || b.status }}
          </span>
        </div>
        <div class="booking-card__details">
          <span>{{ b.slotDate }} {{ b.slotStartTime }}</span>
          <span class="booking-card__price">{{ b.total }} ₽</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.page-header h1 { margin: 0; font-size: 1.6rem; font-weight: 800; color: #1a1a2e; }
.page-header__right { display: flex; align-items: center; gap: 0.75rem; }
.user-name { font-size: 0.9rem; color: #666; }
.btn-primary { padding: 10px 24px; background: #e63946; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; cursor: pointer; }
.btn-outline { padding: 8px 16px; background: white; color: #1a1a2e; border: 1.5px solid #e0e0e0; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; }
.btn-outline:hover { border-color: #1a1a2e; }
.btn-ghost { padding: 8px 16px; background: none; border: none; color: #888; font-size: 0.85rem; cursor: pointer; border-radius: 8px; text-decoration: none; }
.btn-ghost:hover { background: #f5f5f5; color: #1a1a2e; }
.loading { text-align: center; padding: 4rem; color: #999; }
.empty-state { text-align: center; padding: 4rem; }
.empty-state p { color: #999; margin-bottom: 1rem; font-size: 1.1rem; }
.bookings-list { display: flex; flex-direction: column; gap: 0.75rem; }
.booking-card {
  background: white; border: 1px solid #eee; border-radius: 14px;
  padding: 1.25rem 1.5rem; transition: box-shadow 0.2s;
}
.booking-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.booking-card__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.booking-card__service { margin: 0; font-size: 1rem; font-weight: 700; color: #1a1a2e; }
.booking-card__details { display: flex; justify-content: space-between; align-items: center; color: #888; font-size: 0.9rem; }
.booking-card__price { font-weight: 700; color: #e63946; font-size: 1rem; }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.status-badge--pending   { background: #fff8e1; color: #f57f17; }
.status-badge--confirmed { background: #e3f2fd; color: #1565c0; }
.status-badge--completed { background: #e8f5e9; color: #2e7d32; }
.status-badge--cancelled { background: #fce4ec; color: #c62828; }
</style>
