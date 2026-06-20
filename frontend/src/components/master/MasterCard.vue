<!-- src/components/master/MasterCard.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import type { Master } from '@/types/master.types'

const props = defineProps<{ master: Master }>()

const fullName    = computed(() => `${props.master.firstName} ${props.master.lastName}`)
const minPrice    = computed(() =>
    props.master.services.length
        ? Math.min(...props.master.services.map(s => Number(s.price)))
        : null
)
const ratingStr   = computed(() => Number(props.master.rating).toFixed(1))
const distanceStr = computed(() => {
    const d = props.master.distanceKm
    return d != null ? `${Number(d).toFixed(1)} км` : null
})
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
      <span class="master-card__rating" :aria-label="`Рейтинг ${ratingStr}`">
        ★ {{ ratingStr }}
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
                {{ service.name }} — {{ Number(service.price).toLocaleString('ru-RU') }} ₽
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
    padding: 1.5rem; background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;
    transition: all 0.25s; backdrop-filter: blur(10px);
}
.master-card:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(230,57,70,0.3);
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.3);
}

.master-card__header { display: flex; gap: 1rem; margin-bottom: 1rem; }
.master-card__avatar {
    width: 56px; height: 56px; border-radius: 14px; object-fit: cover; flex-shrink: 0;
}
.master-card__avatar-placeholder {
    width: 56px; height: 56px; border-radius: 14px;
    background: linear-gradient(135deg, #e63946, #ff6b6b); flex-shrink: 0;
}
.master-card__meta { flex: 1; min-width: 0; }
.master-card__name { margin: 0 0 4px; font-size: 1.1rem; font-weight: 700; color: white; }
.master-card__verified { color: #4ade80; font-size: 0.8rem; }
.master-card__region { margin: 0; font-size: 0.85rem; color: rgba(255,255,255,0.4); }
.master-card__address { margin: 2px 0 0; font-size: 0.8rem; color: rgba(255,255,255,0.25); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.master-card__stats { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; }
.master-card__rating { font-weight: 700; color: #f59e0b; font-size: 1rem; }
.master-card__reviews { color: rgba(255,255,255,0.3); font-size: 0.85rem; }
.master-card__distance {
    margin-left: auto; padding: 2px 8px; background: rgba(74,222,128,0.1);
    color: #4ade80; border-radius: 6px; font-size: 0.8rem; font-weight: 600;
}

.master-card__services { list-style: none; padding: 0; margin: 0 0 0.75rem; }
.master-card__service {
    font-size: 0.85rem; color: rgba(255,255,255,0.5); padding: 2px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.master-card__service:last-child { border-bottom: none; }

.master-card__price { margin: 0; font-weight: 700; color: #e63946; font-size: 0.95rem; }
</style>