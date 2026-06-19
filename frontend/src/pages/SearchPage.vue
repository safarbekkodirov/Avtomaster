<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mastersApi } from '@/api/masters'
import MasterCard from '@/components/master/MasterCard.vue'
import type { Master, ServiceCategory, Region } from '@/types/master.types'

const route = useRoute()
const router = useRouter()

const loading  = ref(false)
const error    = ref('')
const masters  = ref<Master[]>([])
const page     = ref(1)
const hasMore  = ref(false)

const categories = ref<ServiceCategory[]>([])
const regions    = ref<Region[]>([])

const selectedRegionSlug = ref('')
const selectedCategorySlug = ref((route.query.category as string) || '')
const selectedSort     = ref('rating')

const geoStatus = ref<'idle' | 'detecting' | 'done' | 'error'>('idle')
const geoError  = ref<string | null>(null)
const userLat   = ref<number | null>(null)
const userLng   = ref<number | null>(null)

async function doSearch() {
  loading.value = true
  error.value   = ''
  page.value    = 1
  try {
    const params: Record<string, string | number> = {
      sortBy: selectedSort.value,
      page: 1,
      perPage: 20,
    }
    if (selectedRegionSlug.value) {
      const region = regions.value.find(r => r.slug === selectedRegionSlug.value)
      if (region) params.regionName = region.name
    }
    if (selectedCategorySlug.value) params.categorySlug = selectedCategorySlug.value
    if (userLat.value !== null) params.lat = userLat.value
    if (userLng.value !== null) params.lng = userLng.value

    const result = await mastersApi.search(params as any)
    masters.value = result.data
    hasMore.value = result.pagination.page < result.pagination.totalPages
  } catch (e: any) {
    error.value = e?.response?.data?.detail || 'Ошибка загрузки'
    masters.value = []
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  if (loading.value || !hasMore.value) return
  loading.value = true
  page.value++
  try {
    const params: Record<string, string | number> = {
      sortBy: selectedSort.value,
      page: page.value,
      perPage: 20,
    }
    if (selectedRegionSlug.value) {
      const region = regions.value.find(r => r.slug === selectedRegionSlug.value)
      if (region) params.regionName = region.name
    }
    if (selectedCategorySlug.value) params.categorySlug = selectedCategorySlug.value
    if (userLat.value !== null) params.lat = userLat.value
    if (userLng.value !== null) params.lng = userLng.value

    const result = await mastersApi.search(params as any)
    masters.value = [...masters.value, ...result.data]
    hasMore.value = result.pagination.page < result.pagination.totalPages
  } catch {
    page.value--
  } finally {
    loading.value = false
  }
}

async function loadMeta() {
  try {
    const [catRes, regRes] = await Promise.all([
      mastersApi.getCategories(),
      mastersApi.getRegions(),
    ])
    categories.value = catRes.member
    regions.value    = regRes.member
  } catch { /* ignore */ }
}

async function enableGeo() {
  geoStatus.value = 'detecting'
  geoError.value  = null
  try {
    const coords = await new Promise<GeolocationPosition>((resolve, reject) =>
      navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 5000 })
    )
    userLat.value = coords.coords.latitude
    userLng.value = coords.coords.longitude
    geoStatus.value = 'done'
    selectedSort.value = 'distance'
  } catch {
    geoStatus.value = 'error'
    geoError.value = 'Геолокация недоступна'
  }
}

onMounted(() => {
  loadMeta()
  doSearch()
})
</script>

<template>
  <div class="search-page">
    <header class="search-header">
      <h1>Поиск мастеров</h1>
      <p class="search-subtitle">Найдите лучшего автомастера рядом с вами</p>
      <button class="become-master" @click="router.push({ name: 'register', query: { role: 'master' } })">
        Мастер болсоңуз — катталыңыз
      </button>
    </header>

    <div class="search-filters">
      <div class="filter-group">
        <label>Регион</label>
        <select v-model="selectedRegionSlug">
          <option value="">Все регионы</option>
          <option v-for="r in regions" :key="r.slug" :value="r.slug">{{ r.name }}</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Категория</label>
        <select v-model="selectedCategorySlug">
          <option value="">Все категории</option>
          <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">{{ cat.name }}</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Сортировка</label>
        <select v-model="selectedSort">
          <option value="rating">По рейтингу</option>
          <option value="price">По цене</option>
        </select>
      </div>

      <button class="search-btn" @click="doSearch" :disabled="loading">
        Найти
      </button>

      <button
        class="geo-btn"
        :class="{ 'geo-btn--active': geoStatus === 'done' }"
        :disabled="geoStatus === 'detecting'"
        @click="enableGeo"
      >
        <template v-if="geoStatus === 'idle' || geoStatus === 'error'">Моё местоположение</template>
        <template v-else-if="geoStatus === 'detecting'">Определяю...</template>
        <template v-else>Гео включена</template>
      </button>
    </div>

    <p v-if="error" class="error-msg">{{ error }}</p>

    <div v-if="loading && !masters.length" class="loading">
      <div class="spinner" />
      <span>Загрузка...</span>
    </div>

    <div v-else-if="masters.length" class="masters-grid">
      <MasterCard v-for="master in masters" :key="master.id" :master="master" />
    </div>

    <p v-else class="empty">Мастера не найдены</p>

    <button v-if="hasMore && !loading" class="load-more" @click="loadMore">Показать ещё</button>
  </div>
</template>

<style scoped>
.search-page { padding: 2rem; max-width: 1200px; margin: 0 auto; }

.search-header { text-align: center; margin-bottom: 2rem; }
.search-header h1 { font-size: 2rem; margin: 0 0 0.5rem; color: #1a1a2e; font-weight: 800; }
.search-subtitle { color: #888; font-size: 1.05rem; margin: 0 0 1rem; }
.become-master {
  padding: 10px 24px; background: #1a1a2e; color: white; border: none;
  border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
  transition: background 0.2s;
}
.become-master:hover { background: #2d2d4e; }

.search-filters {
  display: flex; gap: 0.75rem; flex-wrap: wrap;
  align-items: end; margin-bottom: 2rem;
  padding: 1.25rem; background: white; border-radius: 14px;
  border: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.filter-group { display: flex; flex-direction: column; gap: 5px; }
.filter-group label { font-size: 0.8rem; font-weight: 600; color: #555; }
.filter-group select {
  padding: 10px 14px; border: 1.5px solid #e0e0e0; border-radius: 10px;
  font-size: 0.9rem; background: white; outline: none;
}
.filter-group select:focus { border-color: #e63946; }
.search-btn {
  padding: 10px 24px; background: #e63946; color: white; border: none;
  border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 700;
  white-space: nowrap; transition: background 0.2s;
}
.search-btn:hover { background: #d32f3f; }
.search-btn:disabled { opacity: 0.6; }
.geo-btn {
  padding: 10px 18px; background: #f5f5f5; color: #555; border: 1.5px solid #e0e0e0;
  border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 600; white-space: nowrap;
  transition: all 0.2s;
}
.geo-btn:hover { border-color: #1a1a2e; color: #1a1a2e; }
.geo-btn--active { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
.geo-btn:disabled { opacity: 0.6; }

.error-msg { color: #e63946; text-align: center; padding: 1rem; background: #fef2f2; border-radius: 10px; margin-bottom: 1rem; }

.masters-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.loading { display: flex; align-items: center; justify-content: center; gap: 1rem; padding: 3rem; color: #999; }
.spinner {
  width: 24px; height: 24px; border: 3px solid #e5e5e5;
  border-top-color: #e63946; border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty { text-align: center; color: #999; padding: 3rem; font-size: 1.1rem; }

.load-more {
  display: block; margin: 2rem auto 0; padding: 12px 28px;
  background: white; border: 2px solid #e63946; color: #e63946;
  border-radius: 10px; cursor: pointer; font-size: 0.95rem; font-weight: 600;
  transition: all 0.2s;
}
.load-more:hover { background: #e63946; color: white; }
</style>
