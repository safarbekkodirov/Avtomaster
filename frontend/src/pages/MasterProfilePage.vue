<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { mastersApi } from '@/api/masters'
import BookingForm from '@/components/booking/BookingForm.vue'
import ReviewList from '@/components/review/ReviewList.vue'
import type { Master } from '@/types/master.types'

const route  = useRoute()
const master = ref<Master | null>(null)
const loading = ref(true)
const error   = ref('')

const masterId = Number(route.params.id)

onMounted(async () => {
  try {
    const result = await mastersApi.getProfile(masterId)
    master.value = result.data
  } catch {
    error.value = 'Мастер не найден'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="profile-page">
    <!-- Загрузка -->
    <div v-if="loading" class="profile-page__loading">Загрузка...</div>

    <!-- Ошибка -->
    <div v-else-if="error" class="profile-page__error">
      <p>{{ error }}</p>
    </div>

    <!-- Профиль -->
    <template v-else-if="master">
      <!-- Шапка профиля -->
      <header class="profile-header">
        <div class="profile-header__main">
          <img
            v-if="master.avatar"
            :src="master.avatar"
            :alt="`${master.firstName} ${master.lastName}`"
            class="profile-header__avatar"
          />
          <div v-else class="profile-header__avatar-placeholder" />

          <div class="profile-header__info">
            <h1 class="profile-header__name">
              {{ master.firstName }} {{ master.lastName }}
              <span v-if="master.isVerified" class="profile-header__verified" title="Верифицирован">✓</span>
            </h1>
            <p class="profile-header__region">📍 {{ master.regionName }}</p>
            <p v-if="master.address" class="profile-header__address">{{ master.address }}</p>
          </div>
        </div>

        <div class="profile-header__stats">
          <div class="profile-header__rating">
            <span class="profile-header__stars">★ {{ master.rating.toFixed(1) }}</span>
            <span class="profile-header__reviews">({{ master.reviewsCount }} отзывов)</span>
          </div>
        </div>
      </header>

      <!-- О мастере -->
      <section v-if="master.bio" class="profile-section">
        <h2>О мастере</h2>
        <p class="profile-bio">{{ master.bio }}</p>
      </section>

      <!-- Услуги -->
      <section class="profile-section">
        <h2>Услуги</h2>
        <div v-if="master.services.length" class="services-list">
          <div
            v-for="service in master.services"
            :key="service.id"
            class="service-item"
          >
            <div class="service-item__info">
              <span class="service-item__name">{{ service.name }}</span>
              <span class="service-item__duration">{{ service.durationMinutes }} мин</span>
            </div>
            <span class="service-item__price">{{ service.price.toLocaleString('ru-RU') }} ₽</span>
          </div>
        </div>
        <p v-else class="profile-empty">Услуги пока не добавлены</p>
      </section>

      <!-- Запись -->
      <section class="profile-section">
        <BookingForm :master="master" />
      </section>

      <!-- Отзывы -->
      <section class="profile-section">
        <ReviewList :master-id="master.id" />
      </section>
    </template>
  </div>
</template>

<style scoped>
.profile-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.profile-page__loading,
.profile-page__error {
  text-align: center;
  padding: 4rem 1rem;
  color: #666;
  font-size: 1.1rem;
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #eee;
  margin-bottom: 2rem;
}

.profile-header__main {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.profile-header__avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-header__avatar-placeholder {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: #e0e0e0;
}

.profile-header__name {
  font-size: 1.5rem;
  margin: 0 0 0.25rem;
}

.profile-header__verified {
  color: #1976d2;
  font-size: 1rem;
}

.profile-header__region,
.profile-header__address {
  margin: 0.15rem 0;
  color: #666;
  font-size: 0.95rem;
}

.profile-header__rating {
  text-align: right;
  white-space: nowrap;
}

.profile-header__stars {
  font-size: 1.3rem;
  font-weight: 700;
  color: #f59e0b;
}

.profile-header__reviews {
  display: block;
  color: #666;
  font-size: 0.9rem;
}

.profile-section {
  margin-bottom: 2.5rem;
}

.profile-section h2 {
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.profile-bio {
  color: #444;
  line-height: 1.6;
}

.services-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.service-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border: 1px solid #eee;
  border-radius: 8px;
}

.service-item__name {
  font-weight: 600;
}

.service-item__duration {
  color: #888;
  font-size: 0.9rem;
  margin-left: 0.5rem;
}

.service-item__price {
  font-weight: 700;
  color: #e63946;
}

.profile-empty {
  color: #999;
}
</style>
