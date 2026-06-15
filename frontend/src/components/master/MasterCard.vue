<!-- src/components/master/MasterCard.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import type { Master } from '@/types/master.types'

const props = defineProps<{ master: Master }>()

const fullName    = computed(() => `${props.master.firstName} ${props.master.lastName}`)
const minPrice    = computed(() =>
    props.master.services.length
        ? Math.min(...props.master.services.map(s => s.price))
        : null
)
const distanceStr = computed(() =>
    props.master.distanceKm !== null
        ? `${props.master.distanceKm.toFixed(1)} км`
        : null
)
</script>

<template>
    <RouterLink
        :to="{ name: 'master-profile', params: { id: master.id } }"
        class="master-card"
        :aria-label="`Профиль мастера ${fullName}`"
    >
        <div class="master-card__header">
            <img
                v-if="master.avatar"
                :src="master.avatar"
                :alt="fullName"
                class="master-card__avatar"
                loading="lazy"
            />
            <div v-else class="master-card__avatar-placeholder" aria-hidden="true" />

            <div class="master-card__meta">
                <h3 class="master-card__name">
                    {{ fullName }}
                    <span v-if="master.isVerified" class="master-card__verified" title="Верифицирован">✓</span>
                </h3>
                <p class="master-card__region">{{ master.regionName }}</p>
                <p v-if="master.address" class="master-card__address">{{ master.address }}</p>
            </div>
        </div>

        <div class="master-card__stats">
      <span class="master-card__rating" :aria-label="`Рейтинг ${master.rating}`">
        ★ {{ master.rating.toFixed(1) }}
      </span>
            <span class="master-card__reviews">({{ master.reviewsCount }})</span>
            <span v-if="distanceStr" class="master-card__distance">{{ distanceStr }}</span>
        </div>

        <ul v-if="master.services.length" class="master-card__services">
            <li
                v-for="service in master.services.slice(0, 3)"
                :key="service.id"
                class="master-card__service"
            >
                {{ service.name }} — {{ service.price.toLocaleString('ru-RU') }} ₽
            </li>
        </ul>

        <p v-if="minPrice !== null" class="master-card__price">
            от {{ minPrice.toLocaleString('ru-RU') }} ₽
        </p>
    </RouterLink>
</template>

<style scoped>
.master-card {
    display: block; text-decoration: none; color: inherit;
    padding: 1.5rem; border: 1px solid #e5e5e5; border-radius: 12px;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.master-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); border-color: #e63946; }

.master-card__header { display: flex; gap: 1rem; margin-bottom: 1rem; }
.master-card__avatar {
    width: 56px; height: 56px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.master-card__avatar-placeholder {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, #1a1a2e, #e63946); flex-shrink: 0;
}
.master-card__meta { flex: 1; min-width: 0; }
.master-card__name { margin: 0 0 4px; font-size: 1.1rem; font-weight: 700; }
.master-card__verified { color: #28a745; font-size: 0.8rem; }
.master-card__region { margin: 0; font-size: 0.85rem; color: #666; }
.master-card__address { margin: 2px 0 0; font-size: 0.8rem; color: #999; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.master-card__stats { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; }
.master-card__rating { font-weight: 700; color: #f4a261; font-size: 1rem; }
.master-card__reviews { color: #999; font-size: 0.85rem; }
.master-card__distance {
    margin-left: auto; padding: 2px 8px; background: #e8f5e9;
    color: #2e7d32; border-radius: 4px; font-size: 0.8rem; font-weight: 600;
}

.master-card__services { list-style: none; padding: 0; margin: 0 0 0.75rem; }
.master-card__service { font-size: 0.85rem; color: #555; padding: 2px 0; }

.master-card__price { margin: 0; font-weight: 700; color: #e63946; font-size: 0.95rem; }
</style>