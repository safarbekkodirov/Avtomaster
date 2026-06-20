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
    <div class="review-list">
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
                    <div v-if="review.clientAvatar" class="review-list__avatar">
                        <img :src="review.clientAvatar" :alt="`${review.clientFirstName} ${review.clientLastName}`" loading="lazy" />
                    </div>
                    <div v-else class="review-list__avatar-placeholder">
                        {{ review.clientFirstName[0] }}{{ review.clientLastName[0] }}
                    </div>

                    <div class="review-list__meta">
                        <strong>{{ review.clientFirstName }} {{ review.clientLastName }}</strong>
                        <time :datetime="review.createdAt" class="review-list__date">
                            {{ new Date(review.createdAt).toLocaleDateString('ru-RU') }}
                        </time>
                    </div>

                    <div class="review-list__stars">
                        <span
                            v-for="star in 5"
                            :key="star"
                            class="review-list__star"
                            :class="{ 'review-list__star--filled': star <= review.rating }"
                        >★</span>
                    </div>
                </div>

                <p v-if="review.comment" class="review-list__comment">
                    {{ review.comment }}
                </p>
            </li>
        </ul>

        <div v-if="store.loading" class="review-list__loading">
            <div class="spinner" />
            Загрузка отзывов...
        </div>

        <button
            v-if="hasMore && !store.loading"
            type="button"
            class="review-list__more"
            @click="loadMore"
        >
            Показать ещё
        </button>
    </div>
</template>

<style scoped>
.review-list { }

.review-list__empty { color: rgba(255,255,255,0.3); font-size: 0.95rem; margin: 0; }

.review-list__items { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.review-list__item {
    padding: 1rem; background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06); border-radius: 12px;
}

.review-list__header { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.review-list__avatar {
    width: 40px; height: 40px; border-radius: 10px; overflow: hidden; flex-shrink: 0;
}
.review-list__avatar img { width: 100%; height: 100%; object-fit: cover; }
.review-list__avatar-placeholder {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, #e63946, #ff6b6b);
    color: white; font-weight: 700; font-size: 0.8rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.review-list__meta { flex: 1; }
.review-list__meta strong { display: block; font-size: 0.95rem; color: white; }
.review-list__date { font-size: 0.8rem; color: rgba(255,255,255,0.3); }

.review-list__stars { display: flex; gap: 2px; }
.review-list__star { color: rgba(255,255,255,0.1); font-size: 1rem; }
.review-list__star--filled { color: #f59e0b; }

.review-list__comment { margin: 0; color: rgba(255,255,255,0.5); font-size: 0.9rem; line-height: 1.6; }

.review-list__loading {
    display: flex; align-items: center; justify-content: center;
    gap: 8px; color: rgba(255,255,255,0.3); padding: 1rem;
}
.spinner {
    width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.1);
    border-top-color: #e63946; border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.review-list__more {
    display: block; margin: 1rem auto 0; padding: 10px 24px;
    background: transparent; border: 1.5px solid rgba(230,57,70,0.3);
    color: #e63946; border-radius: 10px; cursor: pointer;
    font-size: 0.9rem; font-weight: 600; transition: all 0.2s;
}
.review-list__more:hover {
    background: #e63946; color: white;
}
</style>
