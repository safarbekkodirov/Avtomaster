<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useSearch } from '@/composables/useSearch'
import MasterCard from '@/components/master/MasterCard.vue'

const {
  masters, loading, hasMore, params,
  applyGeo, run, loadMore, updateFilter,
} = useSearch()

const geoStatus = ref<'idle' | 'detecting' | 'done' | 'error'>('idle')
const geoError  = ref<string | null>(null)

const regions = [
  { slug: '',          name: 'Все регионы' },
  { slug: 'bishkek',   name: 'Бишкек' },
  { slug: 'osh',       name: 'Ош' },
  { slug: 'karakol',   name: 'Каракол' },
  { slug: 'tokmok',    name: 'Токмок' },
  { slug: 'jalal-abad', name: 'Джалал-Абад' },
  { slug: 'naryn',     name: 'Нарын' },
  { slug: 'talas',     name: 'Талас' },
]

const radii = [
  { value: 5,   label: '5 км' },
  { value: 10,  label: '10 км' },
  { value: 25,  label: '25 км' },
  { value: 50,  label: '50 км' },
]

async function enableGeo(): Promise<void> {
  geoStatus.value = 'detecting'
  geoError.value  = null
  try {
    await applyGeo()
    geoStatus.value = 'done'
    updateFilter({ sortBy: 'distance' })
    await run()
  } catch {
    geoStatus.value = 'error'
    geoError.value  = 'Разрешите доступ к геолокации'
  }
}

function onRegionChange(slug: string): void {
  updateFilter({ regionSlug: slug || undefined })
  run()
}

function onRadiusChange(radius: number): void {
  if (params.value.lat && params.value.lng) {
    updateFilter({ radiusKm: radius })
    run()
  }
}

function onSortChange(sortBy: string): void {
  updateFilter({ sortBy: sortBy as 'rating' | 'distance' | 'price' })
  run()
}

onMounted(() => run())
</script>

<template>
  <div class="search-page">
    <header class="search-header">
      <h1>Поиск мастеров</h1>
      <p class="search-subtitle">Найдите лучшего автомастера рядом с вами</p>
    </header>

    <div class="search-filters">
      <div class="filter-group">
        <label for="region">Регион</label>
        <select id="region" :value="params.regionSlug ?? ''" @change="onRegionChange(($event.target as HTMLSelectElement).value)">
          <option v-for="r in regions" :key="r.slug" :value="r.slug">{{ r.name }}</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="sort">Сортировка</label>
        <select id="sort" :value="params.sortBy" @change="onSortChange(($event.target as HTMLSelectElement).value)">
          <option value="rating">По рейтингу</option>
          <option value="price">По цене</option>
          <option v-if="geoStatus === 'done'" value="distance">По близости</option>
        </select>
      </div>

      <div v-if="geoStatus === 'done' && params.lat" class="filter-group">
        <label for="radius">Радиус</label>
        <select id="radius" :value="params.radiusKm ?? 10" @change="onRadiusChange(Number(($event.target as HTMLSelectElement).value))">
          <option v-for="r in radii" :key="r.value" :value="r.value">{{ r.label }}</option>
        </select>
      </div>

      <button
        class="geo-btn"
        :disabled="geoStatus === 'detecting'"
        @click="enableGeo"
      >
        <template v-if="geoStatus === 'idle' || geoStatus === 'error'">📍 Моё местоположение</template>
        <template v-else-if="geoStatus === 'detecting'">⏳ Определяю...</template>
        <template v-else>✅ Геолокация включена</template>
      </button>
    </div>

    <p v-if="geoError" class="geo-error">{{ geoError }}</p>

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
.search-header h1 { font-size: 2rem; margin: 0 0 0.5rem; color: #1a1a2e; }
.search-subtitle { color: #666; font-size: 1.1rem; margin: 0; }

.search-filters {
  display: flex; gap: 1rem; flex-wrap: wrap;
  align-items: end; margin-bottom: 2rem;
  padding: 1rem; background: #f8f9fa; border-radius: 8px;
}
.filter-group { display: flex; flex-direction: column; gap: 4px; }
.filter-group label { font-size: 0.8rem; font-weight: 600; color: #555; }
.filter-group select {
  padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;
  font-size: 0.9rem; background: white;
}
.geo-btn {
  padding: 8px 16px; background: #1a1a2e; color: white; border: none;
  border-radius: 6px; cursor: pointer; font-size: 0.9rem; white-space: nowrap;
  transition: background 0.2s;
}
.geo-btn:hover { background: #e63946; }
.geo-btn:disabled { opacity: 0.6; cursor: wait; }
.geo-error { color: #e63946; font-size: 0.9rem; margin-bottom: 1rem; }

.masters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.loading { display: flex; align-items: center; justify-content: center; gap: 1rem; padding: 3rem; color: #666; }
.spinner {
  width: 24px; height: 24px; border: 3px solid #e5e5e5;
  border-top-color: #e63946; border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty { text-align: center; color: #999; padding: 3rem; font-size: 1.1rem; }

.load-more {
  display: block; margin: 2rem auto 0; padding: 10px 24px;
  background: white; border: 2px solid #e63946; color: #e63946;
  border-radius: 6px; cursor: pointer; font-size: 0.95rem; font-weight: 600;
}
.load-more:hover { background: #e63946; color: white; }
</style>
