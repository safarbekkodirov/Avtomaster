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
  <div class="dashboard">
    <!-- Header -->
    <div class="dashboard-hero">
      <div class="dashboard-hero__bg" />
      <div class="dashboard-hero__content">
        <RouterLink to="/" class="back-link">← На главную</RouterLink>
        <h1 class="dashboard-title">Мои записи</h1>
        <p class="dashboard-subtitle">
          {{ auth.user?.firstName }} {{ auth.user?.lastName }}
        </p>

        <div class="dashboard-nav">
          <RouterLink to="/search" class="nav-btn nav-btn--primary">
            🔍 Найти мастера
          </RouterLink>
          <RouterLink to="/notifications" class="nav-btn nav-btn--ghost">
            🔔 Уведомления
          </RouterLink>
          <RouterLink v-if="auth.isMaster" to="/master-dashboard" class="nav-btn nav-btn--ghost">
            🔧 Кабинет мастера
          </RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/admin" class="nav-btn nav-btn--ghost">
            ⚙️ Админ
          </RouterLink>
          <button class="nav-btn nav-btn--ghost" @click="logout">Выйти</button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="dashboard-content">
      <div v-if="booking.loading" class="loading">
        <div class="spinner" />
        Загрузка записей...
      </div>

      <div v-else-if="!booking.bookings.length" class="empty-state">
        <div class="empty-icon">📋</div>
        <h3>Записей пока нет</h3>
        <p>Найдите мастера и запишитесь на обслуживание</p>
        <RouterLink to="/search" class="btn-primary">Найти мастера</RouterLink>
      </div>

      <div v-else class="bookings-list">
        <div
          v-for="b in booking.bookings"
          :key="b.id"
          class="booking-card"
          @click="router.push({ name: 'booking', params: { id: b.id } })"
        >
          <div class="booking-card__header">
            <div class="booking-card__service-info">
              <span class="booking-card__service-icon">🔧</span>
              <h3 class="booking-card__service">{{ b.serviceName }}</h3>
            </div>
            <span class="status-badge" :class="`status-badge--${b.status}`">
              {{ statusLabels[b.status] || b.status }}
            </span>
          </div>
          <div class="booking-card__details">
            <span class="booking-card__detail">📅 {{ b.slotDate }}</span>
            <span class="booking-card__detail">🕐 {{ b.slotStartTime }} — {{ b.slotEndTime }}</span>
            <span class="booking-card__price">{{ Number(b.total).toLocaleString('ru-RU') }} ₽</span>
          </div>
          <div class="booking-card__footer">
            <span class="booking-card__hint">Нажмите для подробностей →</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard { min-height: 100vh; background: #0a0a1a; }

/* Hero */
.dashboard-hero {
  position: relative; padding: 2rem 1.5rem 2.5rem;
  background: linear-gradient(180deg, #0f0f23, #0a0a1a);
  overflow: hidden;
}
.dashboard-hero__bg {
  position: absolute; inset: 0;
}
.dashboard-hero__bg::before {
  content: ''; position: absolute; width: 300px; height: 300px;
  top: -50%; right: -10%; border-radius: 50%;
  background: radial-gradient(circle, rgba(230,57,70,0.15), transparent 70%);
  filter: blur(60px);
}
.dashboard-hero__content { position: relative; z-index: 1; max-width: 900px; margin: 0 auto; }

.back-link {
  display: inline-block; margin-bottom: 1.5rem;
  color: rgba(255,255,255,0.4); text-decoration: none;
  font-size: 0.85rem; font-weight: 500; transition: color 0.2s;
}
.back-link:hover { color: rgba(255,255,255,0.7); }

.dashboard-title {
  font-size: 2rem; font-weight: 800; color: white; margin: 0 0 0.25rem;
  letter-spacing: -0.5px;
}
.dashboard-subtitle {
  color: rgba(255,255,255,0.4); font-size: 0.95rem; margin: 0 0 1.5rem;
}

.dashboard-nav {
  display: flex; flex-wrap: wrap; gap: 0.5rem;
}
.nav-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 10px; font-size: 0.85rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  text-decoration: none; border: none;
}
.nav-btn--primary {
  background: linear-gradient(135deg, #e63946, #d32f3f); color: white;
}
.nav-btn--primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(230,57,70,0.3);
}
.nav-btn--ghost {
  background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5);
  border: 1px solid rgba(255,255,255,0.08);
}
.nav-btn--ghost:hover {
  background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8);
}

/* Content */
.dashboard-content {
  max-width: 900px; margin: 0 auto; padding: 0 1.5rem 3rem;
}

.loading {
  display: flex; align-items: center; justify-content: center;
  gap: 1rem; padding: 4rem; color: rgba(255,255,255,0.4);
}
.spinner {
  width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.1);
  border-top-color: #e63946; border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state {
  text-align: center; padding: 4rem;
}
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.empty-state h3 { color: rgba(255,255,255,0.6); margin: 0 0 0.5rem; }
.empty-state p { color: rgba(255,255,255,0.3); margin: 0 0 1.5rem; font-size: 0.95rem; }
.btn-primary {
  display: inline-block; padding: 12px 28px;
  background: linear-gradient(135deg, #e63946, #d32f3f); color: white;
  border-radius: 12px; font-weight: 700; text-decoration: none;
  transition: all 0.25s;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}

/* Bookings */
.bookings-list { display: flex; flex-direction: column; gap: 0.75rem; }

.booking-card {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; padding: 1.25rem 1.5rem; cursor: pointer;
  transition: all 0.25s; backdrop-filter: blur(10px);
}
.booking-card:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(230,57,70,0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

.booking-card__header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 0.75rem;
}
.booking-card__service-info {
  display: flex; align-items: center; gap: 8px;
}
.booking-card__service-icon { font-size: 1.2rem; }
.booking-card__service {
  margin: 0; font-size: 1.05rem; font-weight: 700; color: white;
}

.booking-card__details {
  display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.booking-card__detail {
  color: rgba(255,255,255,0.4); font-size: 0.9rem;
}
.booking-card__price {
  margin-left: auto; font-weight: 800; color: #e63946; font-size: 1.05rem;
}

.booking-card__footer {
  margin-top: 0.75rem; padding-top: 0.75rem;
  border-top: 1px solid rgba(255,255,255,0.05);
}
.booking-card__hint {
  font-size: 0.8rem; color: rgba(255,255,255,0.2);
}

.status-badge {
  padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
}
.status-badge--pending   { background: rgba(245,158,11,0.15); color: #f59e0b; }
.status-badge--confirmed { background: rgba(59,130,246,0.15); color: #3b82f6; }
.status-badge--completed { background: rgba(34,197,94,0.15); color: #22c55e; }
.status-badge--cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
</style>
