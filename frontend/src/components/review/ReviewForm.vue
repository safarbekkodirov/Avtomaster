<!-- src/components/review/ReviewForm.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useReviewStore } from '@/stores/review.store'

const props = defineProps<{ bookingId: number }>()
const emit  = defineEmits<{ (e: 'submitted'): void }>()

const store   = useReviewStore()
const rating  = ref(0)
const comment = ref('')
const hover   = ref(0)

async function submit(): Promise<void> {
    if (rating.value === 0) return

    await store.create(props.bookingId, {
        rating:  rating.value,
        comment: comment.value.trim() || undefined,
    })

    emit('submitted')
}
</script>

<template>
    <form class="review-form" @submit.prevent="submit">
        <h3 class="review-form__title">Оставить отзыв</h3>

        <!-- Звёздный рейтинг -->
        <fieldset class="review-form__stars" aria-label="Оценка от 1 до 5">
            <legend class="sr-only">Выберите оценку</legend>
            <button
                v-for="star in 5"
                :key="star"
                type="button"
                class="review-form__star"
                :class="{
          'review-form__star--active':  star <= (hover || rating),
          'review-form__star--hovered': star <= hover,
        }"
                :aria-label="`${star} звезд`"
                :aria-pressed="star === rating"
                @click="rating = star"
                @mouseenter="hover = star"
                @mouseleave="hover = 0"
            >★</button>
        </fieldset>

        <p v-if="rating > 0" class="review-form__rating-label">
            {{ ['', 'Ужасно', 'Плохо', 'Нормально', 'Хорошо', 'Отлично'][rating] }}
        </p>

        <!-- Комментарий -->
        <label class="review-form__comment">
            <span>Комментарий (необязательно)</span>
            <textarea
                v-model="comment"
                maxlength="1000"
                rows="4"
                placeholder="Расскажите о своём опыте..."
            />
            <span class="review-form__counter">{{ comment.length }}/1000</span>
        </label>

        <p v-if="store.error" class="review-form__error" role="alert">
            {{ store.error }}
        </p>

        <button
            type="submit"
            class="review-form__submit"
            :disabled="rating === 0 || store.loading"
            :aria-busy="store.loading"
        >
            {{ store.loading ? 'Отправка...' : 'Отправить отзыв' }}
        </button>
    </form>
</template>

<style scoped>
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

.review-form { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e5e5; }
.review-form__title { margin: 0 0 1rem; font-size: 1.1rem; }

.review-form__stars { border: none; padding: 0; margin: 0 0 0.5rem; display: flex; gap: 4px; }
.review-form__star {
    font-size: 2rem; color: #ddd; background: none; border: none;
    cursor: pointer; transition: color 0.15s; padding: 0 2px;
}
.review-form__star--active { color: #f59e0b; }
.review-form__star--hovered { color: #fbbf24; }

.review-form__rating-label { margin: 0 0 1rem; color: #666; font-size: 0.9rem; }

.review-form__comment { display: block; margin: 0 0 1rem; }
.review-form__comment span:first-child { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
.review-form__comment textarea {
    width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;
    font-size: 0.9rem; resize: vertical; font-family: inherit;
}
.review-form__counter { display: block; text-align: right; font-size: 0.75rem; color: #999; margin-top: 2px; }

.review-form__error { color: #e63946; font-size: 0.9rem; margin: 0 0 0.5rem; }

.review-form__submit {
    width: 100%; padding: 12px; background: #1a1a2e; color: white;
    border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600;
    cursor: pointer; transition: background 0.2s;
}
.review-form__submit:hover:not(:disabled) { background: #e63946; }
.review-form__submit:disabled { opacity: 0.5; cursor: wait; }
</style>