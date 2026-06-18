<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useBookingStore } from '@/stores/booking.store'
import { useRouter } from 'vue-router'
import NotificationBell from '@/components/common/NotificationBell.vue'

const auth    = useAuthStore()
const booking = useBookingStore()
const router  = useRouter()

onMounted(() => booking.fetchList())

async function logout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="dashboard">
    <header class="dashboard__header">
      <h1>Личный кабинет</h1>
      <div class="dashboard__actions">
        <RouterLink v-if="auth.isMaster" to="/master-dashboard" class="btn-master">Кабинет мастера</RouterLink>
        <NotificationBell />
        <RouterLink to="/notifications" class="btn-notif">Уведомления</RouterLink>
        <span>{{ auth.user?.firstName }} {{ auth.user?.lastName }}</span>
        <button @click="logout">Выйти</button>
      </div>
    </header>

    <section>
      <h2>Мои записи</h2>
      <div v-if="booking.loading">Загрузка...</div>
      <div v-else-if="!booking.bookings.length">Записей нет</div>
      <div v-else class="bookings-list">
        <div v-for="b in booking.bookings" :key="b.id" class="booking-item">
          <strong>{{ b.serviceName }}</strong>
          <span>{{ b.slotDate }} {{ b.slotStartTime }}</span>
          <span class="status" :class="`status--${b.status}`">{{ b.status }}</span>
          <span>{{ b.total }} ₽</span>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dashboard { padding:2rem; max-width:900px; margin:0 auto; }
.dashboard__header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
.dashboard__actions { display:flex; align-items:center; gap:1rem; }
.btn-master { padding:8px 16px; background:#1a1a2e; color:white; border:none; border-radius:6px; text-decoration:none; font-size:0.9rem; }
.btn-master:hover { background:#2d2d4e; }
.btn-notif { padding:8px 16px; background:white; color:#1a1a2e; border:1px solid #ddd; border-radius:6px; text-decoration:none; font-size:0.9rem; }
.btn-notif:hover { border-color:#1a1a2e; }
.dashboard__header button { padding:8px 16px; background:#e63946; color:white; border:none; border-radius:6px; cursor:pointer; }
.bookings-list { display:flex; flex-direction:column; gap:.5rem; }
.booking-item { display:flex; gap:1rem; align-items:center; padding:1rem; border:1px solid #eee; border-radius:8px; }
.status { padding:2px 8px; border-radius:4px; font-size:.85rem; }
.status--pending   { background:#fff3cd; color:#856404; }
.status--confirmed { background:#cce5ff; color:#004085; }
.status--completed { background:#d4edda; color:#155724; }
.status--cancelled { background:#f8d7da; color:#721c24; }
</style>
