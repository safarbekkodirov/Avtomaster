<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mastersApi } from '@/api/masters'
import BookingForm from '@/components/booking/BookingForm.vue'
import ReviewList from '@/components/review/ReviewList.vue'
import MapDisplay from '@/components/common/MapDisplay.vue'
import type { Master } from '@/types/master.types'

const route  = useRoute()
const router = useRouter()
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
  <div class="page">
    <button class="btn-back" @click="router.back()">← Назад</button>

    <div v-if="loading" class="loading">Загрузка...</div>

    <div v-else-if="error" class="error-page">
      <p>{{ error }}</p>
    </div>

    <template v-else-if="master">
      <!-- Hero -->
      <div class="profile-hero">
        <div class="profile-hero__left">
          <div class="profile-hero__avatar">
            {{ master.firstName[0] }}{{ master.lastName[0] }}
          </div>
          <div class="profile-hero__info">
            <h1 class="profile-hero__name">
              {{ master.firstName }} {{ master.lastName }}
              <span v-if="master.isVerified" class="verified-badge">Верифицирован</span>
            </h1>
            <p class="profile-hero__region">{{ master.regionName }}{{ master.address ? ', ' + master.address : '' }}</p>
            <div class="profile-hero__rating">
              <span class="stars">{{ Number(master.rating).toFixed(1) }}</span>
              <span class="reviews">({{ master.reviewsCount }} отзывов)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Байланыш -->
      <div class="contact-bar" v-if="master.phone">
        <a :href="'tel:' + master.phone" class="contact-item contact-item--phone">
          {{ master.phone }}
        </a>
      </div>

      <!-- О мастере -->
      <section v-if="master.bio" class="card">
        <h2 class="card__title">О мастере</h2>
        <p class="card__text">{{ master.bio }}</p>
      </section>

      <!-- Услуги -->
      <section class="card">
        <h2 class="card__title">Услуги</h2>
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
            <span class="service-item__price">{{ Number(service.price).toLocaleString('ru-RU') }} ₽</span>
          </div>
        </div>
        <p v-else class="empty-text">Услуги пока не добавлены</p>
      </section>

      <!-- Местоположение -->
      <section v-if="master.lat && master.lng" class="card">
        <h2 class="card__title">Местоположение</h2>
        <MapDisplay
          :lat="Number(master.lat)"
          :lng="Number(master.lng)"
          :zoom="15"
          :popup="`${master.firstName} ${master.lastName}`"
        />
      </section>

      <!-- Запись -->
      <section class="card">
        <BookingForm :master="master" />
      </section>

      <!-- Отзывы -->
      <section class="card">
        <ReviewList :master-id="master.id" />
      </section>
    </template>
  </div>
</template>

<style scoped>
.page { max-width: 800px; margin: 0 auto; padding: 1.5rem; }
.btn-back {
  padding: 8px 16px; background: #f5f5f5; border: none; border-radius: 8px;
  color: #555; font-size: 0.85rem; cursor: pointer; margin-bottom: 1.5rem;
}
.btn-back:hover { background: #eee; color: #1a1a2e; }
.loading { text-align: center; padding: 4rem; color: #999; }
.error-page { text-align: center; padding: 4rem; color: #999; }

.profile-hero {
  background: white; border-radius: 16px; padding: 1.5rem;
  border: 1px solid #eee; margin-bottom: 1rem;
}
.profile-hero__left { display: flex; gap: 1.25rem; align-items: center; }
.profile-hero__avatar {
  width: 72px; height: 72px; border-radius: 16px; flex-shrink: 0;
  background: linear-gradient(135deg, #1a1a2e, #e63946);
  color: white; font-weight: 800; font-size: 1.3rem;
  display: flex; align-items: center; justify-content: center;
}
.profile-hero__name {
  margin: 0 0 0.25rem; font-size: 1.4rem; font-weight: 800; color: #1a1a2e;
  display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.verified-badge {
  padding: 3px 10px; background: #e8f5e9; color: #2e7d32; border-radius: 6px;
  font-size: 0.7rem; font-weight: 700;
}
.profile-hero__region { margin: 0 0 0.5rem; color: #888; font-size: 0.9rem; }
.profile-hero__rating { display: flex; align-items: center; gap: 0.5rem; }
.stars { font-weight: 800; color: #f59e0b; font-size: 1.1rem; }
.reviews { color: #aaa; font-size: 0.85rem; }

.contact-bar {
  display: flex; gap: 0.75rem; margin-bottom: 1rem;
}
.contact-item {
  display: flex; align-items: center; gap: 8px;
  padding: 12px 20px; background: white; border: 1.5px solid #eee;
  border-radius: 12px; text-decoration: none; color: #1a1a2e;
  font-weight: 600; font-size: 0.95rem; transition: all 0.2s;
}
.contact-item--phone { border-color: #e63946; color: #e63946; }
.contact-item--phone:hover { background: #e63946; color: white; }

.card {
  background: white; border-radius: 16px; padding: 1.5rem;
  border: 1px solid #eee; margin-bottom: 1rem;
}
.card__title { margin: 0 0 1rem; font-size: 1.1rem; font-weight: 700; color: #1a1a2e; }
.card__text { margin: 0; color: #555; line-height: 1.6; }

.services-list { display: flex; flex-direction: column; gap: 0.5rem; }
.service-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.85rem 1rem; background: #f9f9f9; border-radius: 10px;
}
.service-item__left { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.service-item__name { font-weight: 700; color: #1a1a2e; }
.service-item__meta { color: #aaa; font-size: 0.85rem; }
.service-item__cat {
  padding: 2px 10px; background: white; border: 1px solid #eee;
  border-radius: 6px; font-size: 0.75rem; color: #666;
}
.service-item__price { font-weight: 800; color: #e63946; font-size: 1rem; }

.empty-text { color: #aaa; margin: 0; }
</style>
