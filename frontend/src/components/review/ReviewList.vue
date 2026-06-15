<!-- src/components/review/ReviewList.vue -->
<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useReviewStore } from '@/stores/review.store'

const props = defineProps<{ masterId: number }>()

const store      = useReviewStore()
const hasMore    = computed(() =>
    store.pagination !== null &&
    store.pagination.page < store.pagination.totalPages
)

onMounted(() => store.fetchMasterReviews(props.masterId))

function loadMore(): void {
    if (!store.pagination) return
    store.fetchMasterReviews(props.masterId, store.pagination.page + 1)
}
</script>

<template>
    <section class="review-list" aria-label="Отзывы о мастере">
        <h2 class="review-list__title">
            Отзывы
            <span v-if="store.pagination" class="review-list__count">
        ({{ store.pagination.total }})
      </span>
        </h2>

        <p v-if="!store.loading && store.reviews.length === 0" class="review-list__empty">
            Отзывов пока нет. Станьте первым!
        </p>

        <ul class="review-list__items">
            <li
                v-for="review in store.reviews"
                :key="review.id"
                class="review-list__item"
            >
                <div class="review-list__header">
                    <img
                        v-if="review.clientAvatar"
                        :src="review.clientAvatar"
                        :alt="`${review.clientFirstName} ${review.clientLastName}`"
                        class="review-list__avatar"
                        loading="lazy"
                    />
                    <div v-else class="review-list__avatar-placeholder" aria-hidden="true" />

                    <div class="review-list__meta">
                        <strong>{{ review.clientFirstName }} {{ review.clientLastName }}</strong>
                        <time :datetime="review.createdAt" class="review-list__date">
                            {{ new Date(review.createdAt).toLocaleDateString('ru-RU') }}
                        </time>
                    </div>

                    <div class="review-list__stars" :aria-label="`Оценка: ${review.rating} из 5`">
            <span
                v-for="star in 5"
                :key="star"
                class="review-list__star"
                :class="{ 'review-list__star--filled': star <= review.rating }"
                aria-hidden="true"
            >★</span>
                    </div>
                </div>

                <p v-if="review.comment" class="review-list__comment">
                    {{ review.comment }}
                </p>
            </li>
        </ul>

        <div v-if="store.loading" class="review-list__loading" aria-live="polite">
            Загрузка...
        </div>

        <button
            v-if="hasMore && !store.loading"
            type="button"
            class="review-list__more"
            @click="loadMore"
        >
            Показать ещё
        </button>
    </section>
</template>

<style scoped>
.review-list { }
.review-list__title { margin: 0 0 1rem; font-size: 1.2rem; }
.review-list__count { color: #999; font-weight: 400; font-size: 0.95rem; }
.review-list__empty { color: #999; font-size: 0.95rem; }

.review-list__items { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
.review-list__item { padding: 1rem; border: 1px solid #e5e5e5; border-radius: 8px; }

.review-list__header { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.review-list__avatar {
    width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.review-list__avatar-placeholder {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2); flex-shrink: 0;
}
.review-list__meta { flex: 1; }
.review-list__meta strong { display: block; font-size: 0.95rem; }
.review-list__date { font-size: 0.8rem; color: #999; }

.review-list__stars { display: flex; gap: 2px; }
.review-list__star { color: #ddd; font-size: 1rem; }
.review-list__star--filled { color: #f59e0b; }

.review-list__comment { margin: 0; color: #444; font-size: 0.9rem; line-height: 1.5; }

.review-list__loading { text-align: center; color: #999; padding: 1rem; }
.review-list__more {
    display: block; margin: 1rem auto 0; padding: 8px 20px;
    background: white; border: 1px solid #e63946; color: #e63946;
    border-radius: 6px; cursor: pointer; font-size: 0.9rem;
}
.review-list__more:hover { background: #e63946; color: white; }
</style>