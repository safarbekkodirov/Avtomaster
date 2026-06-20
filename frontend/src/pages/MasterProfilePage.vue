<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { mastersApi } from '@/api/masters'
import BookingForm from '@/components/booking/BookingForm.vue'
import QuickBookingForm from '@/components/booking/QuickBookingForm.vue'
import ReviewList from '@/components/review/ReviewList.vue'
import MapDisplay from '@/components/common/MapDisplay.vue'
import type { Master } from '@/types/master.types'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const master = ref<Master | null>(null)
const loading = ref(true)
const error   = ref('')

const masterId = Number(route.params.id)

onMounted(async () => {
  try {
    const result = await mastersApi.getProfile(masterId)
    master.value = result
  } catch {
    error.value = 'Мастер не найден'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="profile-page">
    <!-- Header -->
    <div class="profile-hero">
      <div class="profile-hero__bg">
        <div class="profile-hero__orb" />
      </div>

      <div class="profile-hero__content">
        <RouterLink to="/search" class="back-link">← Назад к поиск</RouterLink>

        <div v-if="loading" class="loading">
          <div class="spinner" />
          <span>Загрузка...</span>
        </div>

        <div v-else-if="error" class="error-msg">
          <div class="error-icon">😔</div>
          <p>{{ error }}</p>
        </div>

        <template v-else-if="master">
          <div class="profile-header">
            <div class="profile-avatar">
              <span>{{ master.firstName[0] }}{{ master.lastName[0] }}</span>
              <div v-if="master.isVerified" class="avatar-badge">✓</div>
            </div>

            <div class="profile-info">
              <h1 class="profile-name">
                {{ master.firstName }} {{ master.lastName }}
              </h1>
              <p class="profile-location">
                📍 {{ master.regionName }}{{ master.address ? ', ' + master.address : '' }}
              </p>
              <div class="profile-rating">
                <div class="stars">
                  <span v-for="s in 5" :key="s" class="star" :class="{ 'star--filled': s <= Math.round(Number(master.rating)) }">★</span>
                </div>
                <span class="rating-num">{{ Number(master.rating).toFixed(1) }}</span>
                <span class="rating-count">({{ master.reviewsCount }} отзывов)</span>
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div class="contact-actions" v-if="master.phone">
            <a :href="'tel:' + master.phone" class="contact-btn contact-btn--phone">
              📞 {{ master.phone }}
            </a>
          </div>
        </template>
      </div>
    </div>

    <!-- Content -->
    <div class="profile-content" v-if="!loading && master">
      <!-- Bio -->
      <section v-if="master.bio" class="content-card">
        <div class="content-card__header">
          <span class="content-card__icon">👤</span>
          <h2 class="content-card__title">О мастере</h2>
        </div>
        <p class="content-card__text">{{ master.bio }}</p>
      </section>

      <!-- Services -->
      <section class="content-card">
        <div class="content-card__header">
          <span class="content-card__icon">🔧</span>
          <h2 class="content-card__title">Услуги</h2>
        </div>
        <div v-if="master.services.length" class="services-list">
          <div
            v-for="service in master.services"
            :key="service.id"
            class="service-item"
          >
            <div class="service-item__left">
              <span class="service-item__name">{{ service.name }}</span>
              <span class="service-item__meta">{{ service.durationMinutes }} мин</span>
              <span v-if="service.category" class="service-item__cat">{{ service.category.name }}</span>
            </div>
            <span class="service-item__price">{{ Number(service.price).toLocaleString('ru-RU') }} сом</span>
          </div>
        </div>
        <p v-else class="empty-text">Услуги пока не добавлены</p>
      </section>

      <!-- Map -->
      <section v-if="master.lat && master.lng" class="content-card">
        <div class="content-card__header">
          <span class="content-card__icon">📍</span>
          <h2 class="content-card__title">Местоположение</h2>
        </div>
        <div class="map-wrapper">
          <MapDisplay
            :lat="Number(master.lat)"
            :lng="Number(master.lng)"
            :zoom="15"
            :popup="`${master.firstName} ${master.lastName}`"
          />
        </div>
      </section>

      <!-- Booking -->
      <section class="content-card content-card--accent">
        <div class="content-card__header">
          <span class="content-card__icon">📅</span>
          <h2 class="content-card__title">Записаться</h2>
        </div>
        <QuickBookingForm :master="master" />
      </section>

      <!-- Reviews -->
      <section class="content-card">
        <div class="content-card__header">
          <span class="content-card__icon">⭐</span>
          <h2 class="content-card__title">Отзывы</h2>
        </div>
        <ReviewList :master-id="master.id" />
      </section>
    </div>
  </div>
</template>

<style scoped>
.profile-page { min-height: 100vh; background: #0a0a1a; }

/* Hero */
.profile-hero {
  position: relative; padding: 2rem 1.5rem 3rem;
  background: linear-gradient(180deg, #0f0f23, #0a0a1a);
  overflow: hidden;
}
.profile-hero__bg {
  position: absolute; inset: 0;
}
.profile-hero__orb {
  position: absolute; width: 400px; height: 400px;
  top: -40%; right: -10%; border-radius: 50%;
  background: radial-gradient(circle, rgba(230,57,70,0.15), transparent 70%);
  filter: blur(60px); animation: floatOrb 10s ease-in-out infinite;
}
@keyframes floatOrb {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(20px, -15px) scale(1.05); }
}

.profile-hero__content {
  position: relative; z-index: 1;
  max-width: 800px; margin: 0 auto;
}

.back-link {
  display: inline-block; margin-bottom: 2rem;
  color: rgba(255,255,255,0.4); text-decoration: none;
  font-size: 0.85rem; font-weight: 500; transition: color 0.2s;
}
.back-link:hover { color: rgba(255,255,255,0.7); }

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

.error-msg { text-align: center; padding: 4rem; }
.error-icon { font-size: 3rem; margin-bottom: 1rem; }
.error-msg p { color: rgba(255,255,255,0.4); font-size: 1.1rem; }

/* Profile Header */
.profile-header {
  display: flex; align-items: center; gap: 1.5rem;
}
.profile-avatar {
  position: relative; width: 80px; height: 80px; flex-shrink: 0;
  border-radius: 20px;
  background: linear-gradient(135deg, #e63946, #ff6b6b);
  color: white; font-weight: 900; font-size: 1.5rem;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}
.avatar-badge {
  position: absolute; bottom: -4px; right: -4px;
  width: 24px; height: 24px; border-radius: 50%;
  background: #4ade80; color: white;
  font-size: 0.7rem; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #0a0a1a;
}
.profile-info { flex: 1; min-width: 0; }
.profile-name {
  margin: 0 0 0.25rem; font-size: 1.8rem; font-weight: 800;
  color: white; letter-spacing: -0.5px;
}
.profile-location {
  margin: 0 0 0.75rem; color: rgba(255,255,255,0.4); font-size: 0.9rem;
}
.profile-rating { display: flex; align-items: center; gap: 8px; }
.stars { display: flex; gap: 2px; }
.star { color: rgba(255,255,255,0.15); font-size: 1rem; }
.star--filled { color: #f59e0b; }
.rating-num { font-weight: 800; color: #f59e0b; font-size: 1.1rem; }
.rating-count { color: rgba(255,255,255,0.3); font-size: 0.85rem; }

/* Contact */
.contact-actions {
  display: flex; gap: 0.75rem; margin-top: 1.5rem;
}
.contact-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 12px 24px; border-radius: 12px;
  text-decoration: none; font-weight: 700; font-size: 0.95rem;
  transition: all 0.25s;
}
.contact-btn--phone {
  background: rgba(230,57,70,0.15); border: 1.5px solid rgba(230,57,70,0.3);
  color: #e63946;
}
.contact-btn--phone:hover {
  background: #e63946; color: white;
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}

/* Content */
.profile-content {
  max-width: 800px; margin: 0 auto; padding: 0 1.5rem 3rem;
  display: flex; flex-direction: column; gap: 1rem;
}
.content-card {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px; padding: 1.5rem; backdrop-filter: blur(10px);
}
.content-card--accent {
  border-color: rgba(230,57,70,0.2);
}
.content-card__header {
  display: flex; align-items: center; gap: 10px; margin-bottom: 1rem;
}
.content-card__icon { font-size: 1.3rem; }
.content-card__title {
  margin: 0; font-size: 1.15rem; font-weight: 700; color: white;
}
.content-card__text {
  margin: 0; color: rgba(255,255,255,0.5); line-height: 1.7; font-size: 0.95rem;
}

/* Services */
.services-list { display: flex; flex-direction: column; gap: 0.5rem; }
.service-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.85rem 1rem; background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.06); border-radius: 12px;
}
.service-item__left { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.service-item__name { font-weight: 700; color: white; font-size: 0.95rem; }
.service-item__meta { color: rgba(255,255,255,0.3); font-size: 0.85rem; }
.service-item__cat {
  padding: 2px 10px; background: rgba(255,255,255,0.06);
  border-radius: 6px; font-size: 0.75rem; color: rgba(255,255,255,0.4);
}
.service-item__price { font-weight: 800; color: #e63946; font-size: 1rem; }

/* Map */
.map-wrapper {
  border-radius: 12px; overflow: hidden;
  border: 1px solid rgba(255,255,255,0.08);
}

.empty-text { color: rgba(255,255,255,0.3); margin: 0; }

.auth-prompt {
  text-align: center; padding: 1.5rem;
}
.auth-prompt p {
  margin: 0 0 1.25rem; color: rgba(255,255,255,0.5); font-size: 0.95rem;
}
.auth-prompt__buttons {
  display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;
}
.auth-btn {
  padding: 12px 28px; border-radius: 12px; font-size: 0.95rem;
  font-weight: 700; cursor: pointer; transition: all 0.25s; border: none;
}
.auth-btn--primary {
  background: linear-gradient(135deg, #e63946, #d32f3f); color: white;
}
.auth-btn--primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}
.auth-btn--outline {
  background: rgba(255,255,255,0.08); color: white;
  border: 1.5px solid rgba(255,255,255,0.2);
}
.auth-btn--outline:hover {
  background: rgba(255,255,255,0.15);
  border-color: rgba(255,255,255,0.3);
}

@media (max-width: 640px) {
  .profile-header { flex-direction: column; text-align: center; }
  .profile-rating { justify-content: center; }
  .contact-actions { justify-content: center; }
}
</style>
