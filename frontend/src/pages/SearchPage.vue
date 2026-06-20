<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
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
const totalResults = ref(0)

const categories = ref<ServiceCategory[]>([])
const regions    = ref<Region[]>([])

const selectedRegionSlug = ref('')
const selectedCategorySlug = ref((route.query.category as string) || '')

const geoStatus = ref<'idle' | 'detecting' | 'done' | 'error'>('idle')
const userLat   = ref<number | null>(null)
const userLng   = ref<number | null>(null)

async function doSearch() {
  loading.value = true
  error.value   = ''
  page.value    = 1
  try {
    const params: Record<string, string | number> = { page: 1, perPage: 20 }
    if (selectedRegionSlug.value) {
      const region = regions.value.find(r => r.slug === selectedRegionSlug.value)
      if (region) params.regionName = region.name
    }
    if (selectedCategorySlug.value) params.categorySlug = selectedCategorySlug.value
    if (userLat.value !== null) params.lat = userLat.value
    if (userLng.value !== null) params.lng = userLng.value

    const result = await mastersApi.search(params as any)
    masters.value = result.data
    totalResults.value = result.pagination.total
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
    const params: Record<string, string | number> = { page: page.value, perPage: 20 }
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
  } catch { page.value-- } finally { loading.value = false }
}

async function loadMeta() {
  try {
    const [catRes, regRes] = await Promise.all([
      mastersApi.getCategories(),
      mastersApi.getRegions(),
    ])
    categories.value = catRes.member
    regions.value    = regRes.member
  } catch {}
}

async function enableGeo() {
  geoStatus.value = 'detecting'
  try {
    const coords = await new Promise<GeolocationPosition>((resolve, reject) =>
      navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 5000 })
    )
    userLat.value = coords.coords.latitude
    userLng.value = coords.coords.longitude
    geoStatus.value = 'done'
  } catch {
    geoStatus.value = 'error'
  }
}

onMounted(() => { loadMeta(); doSearch() })
</script>

<template>
  <div class="search-page">
    <!-- Header -->
    <div class="search-hero">
      <div class="search-hero__bg" />
      <div class="search-hero__content">
        <RouterLink to="/" class="search-hero__back">← На главную</RouterLink>
        <h1 class="search-hero__title">Поиск мастеров</h1>
        <p class="search-hero__subtitle">Найдите лучшего автомастера рядом с вами</p>

        <!-- Filters -->
        <div class="filters">
          <div class="filter-group">
            <label>📍 Регион</label>
            <select v-model="selectedRegionSlug" @change="doSearch">
              <option value="">Все регионы</option>
              <option v-for="r in regions" :key="r.slug" :value="r.slug">{{ r.name }}</option>
            </select>
          </div>

          <div class="filter-group">
            <label>🔧 Категория</label>
            <select v-model="selectedCategorySlug" @change="doSearch">
              <option value="">Все категории</option>
              <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">{{ cat.name }}</option>
            </select>
          </div>

          <button class="geo-btn" :class="{ 'geo-btn--active': geoStatus === 'done' }"
            :disabled="geoStatus === 'detecting'" @click="enableGeo">
            <template v-if="geoStatus === 'idle' || geoStatus === 'error'">📍 Моя гео</template>
            <template v-else-if="geoStatus === 'detecting'">⏳</template>
            <template v-else>✅ Гео вкл.</template>
          </button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="search-results">
      <div class="results-header" v-if="!loading">
        <span class="results-count" v-if="masters.length">
          Найдено <strong>{{ totalResults }}</strong> мастер(ов)
        </span>
        <RouterLink to="/register" class="become-master" v-if="masters.length">
          Стать мастером →
        </RouterLink>
      </div>

      <p v-if="error" class="error-msg">{{ error }}</p>

      <div v-if="loading && !masters.length" class="loading">
        <div class="spinner" />
        <span>Ищем мастеров...</span>
      </div>

      <div v-else-if="masters.length" class="masters-grid">
        <MasterCard v-for="master in masters" :key="master.id" :master="master" />
      </div>

      <div v-else-if="!loading" class="empty">
        <div class="empty__icon">🔍</div>
        <h3>Мастера не найдены</h3>
        <p>Попробуйте изменить параметры поиска</p>
      </div>

      <button v-if="hasMore && !loading" class="load-more" @click="loadMore">
        Показать ещё
      </button>
    </div>
  </div>
</template>

<style scoped>
.search-page { min-height: 100vh; background: #0a0a1a; }

/* Hero */
.search-hero {
  position: relative; padding: 3rem 1.5rem 2rem;
  background: linear-gradient(180deg, #0f0f23, #0a0a1a);
}
.search-hero__bg {
  position: absolute; inset: 0; overflow: hidden;
}
.search-hero__bg::before {
  content: ''; position: absolute; width: 400px; height: 400px;
  top: -50%; right: -10%; border-radius: 50%;
  background: radial-gradient(circle, rgba(230,57,70,0.15), transparent 70%);
  filter: blur(60px);
}
.search-hero__content {
  position: relative; z-index: 1;
  max-width: 900px; margin: 0 auto;
}
.search-hero__back {
  display: inline-block; margin-bottom: 1.5rem;
  color: rgba(255,255,255,0.4); text-decoration: none;
  font-size: 0.85rem; font-weight: 500; transition: color 0.2s;
}
.search-hero__back:hover { color: rgba(255,255,255,0.7); }

.search-hero__title {
  font-size: 2.2rem; font-weight: 800; color: white; margin: 0 0 0.5rem;
  letter-spacing: -0.5px;
}
.search-hero__subtitle {
  color: rgba(255,255,255,0.4); font-size: 1rem; margin: 0 0 1.5rem;
}

/* Filters */
.filters {
  display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;
  padding: 1rem; background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;
  backdrop-filter: blur(10px);
}
.filter-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 180px; }
.filter-group label {
  font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.5);
  text-transform: uppercase; letter-spacing: 0.5px;
}
.filter-group select {
  padding: 12px 14px; border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 10px; font-size: 0.9rem;
  background: rgba(255,255,255,0.06); color: white;
  outline: none; transition: all 0.2s;
}
.filter-group select option { background: #1a1a2e; color: white; }
.filter-group select:focus {
  border-color: rgba(230,57,70,0.5);
  box-shadow: 0 0 0 3px rgba(230,57,70,0.1);
}
.geo-btn {
  padding: 12px 18px; background: rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.5); border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 600;
  white-space: nowrap; transition: all 0.2s;
}
.geo-btn:hover { border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.8); }
.geo-btn--active {
  background: rgba(74,222,128,0.1); border-color: rgba(74,222,128,0.3);
  color: #4ade80;
}
.geo-btn:disabled { opacity: 0.6; }

/* Results */
.search-results {
  max-width: 1200px; margin: 0 auto; padding: 0 1.5rem 3rem;
}
.results-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
}
.results-count {
  color: rgba(255,255,255,0.4); font-size: 0.9rem;
}
.results-count strong { color: rgba(255,255,255,0.8); }
.become-master {
  color: #e63946; font-size: 0.85rem; font-weight: 600;
  text-decoration: none; transition: color 0.2s;
}
.become-master:hover { color: #ff6b6b; }

.error-msg {
  color: #ff6b6b; text-align: center; padding: 1rem;
  background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.2);
  border-radius: 12px; margin-bottom: 1rem;
}

.masters-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.25rem;
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

.empty {
  text-align: center; padding: 4rem; color: rgba(255,255,255,0.3);
}
.empty__icon { font-size: 3rem; margin-bottom: 1rem; }
.empty h3 { color: rgba(255,255,255,0.6); margin: 0 0 0.5rem; }
.empty p { margin: 0; font-size: 0.9rem; }

.load-more {
  display: block; margin: 2rem auto 0; padding: 14px 32px;
  background: transparent; border: 2px solid rgba(230,57,70,0.4);
  color: #e63946; border-radius: 12px; cursor: pointer;
  font-size: 0.95rem; font-weight: 700; transition: all 0.25s;
}
.load-more:hover {
  background: #e63946; color: white;
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}

@media (max-width: 768px) {
  .search-hero__title { font-size: 1.6rem; }
  .filters { flex-direction: column; }
  .filter-group { min-width: 100%; }
  .masters-grid { grid-template-columns: 1fr; }
}
</style>
