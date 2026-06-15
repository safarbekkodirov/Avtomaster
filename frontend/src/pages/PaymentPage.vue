<!-- src/pages/PaymentPage.vue -->
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePaymentStore } from '@/stores/payment.store'
import { useBookingStore } from '@/stores/booking.store'

const route        = useRoute()
const router       = useRouter()
const paymentStore = usePaymentStore()
const bookingStore = useBookingStore()

const bookingId = Number(route.params.bookingId)
const status    = route.query.status as string | undefined  // success | cancel

// Страница используется и для инициализации, и для возврата с Stripe
onMounted(async () => {
    if (status === 'success') {
        // Stripe вернул пользователя — опрашиваем статус бронирования
        await bookingStore.fetchOne(bookingId)
        return
    }

    if (status === 'cancel') {
        // Пользователь закрыл Stripe Checkout
        return
    }

    // Нет статуса — инициализируем оплату и редиректим на Stripe
    try {
        const checkoutUrl = await paymentStore.initiate(bookingId)
        window.location.href = checkoutUrl  // полный редирект, не router.push
    } catch {
        // Ошибка — остаёмся на странице с сообщением
    }
})

function retry(): void {
    paymentStore.initiate(bookingId).then(url => {
        window.location.href = url
    })
}
</script>

<template>
    <div class="payment-page">
        <!-- Редирект на Stripe -->
        <div v-if="!status && paymentStore.loading" class="payment-page__redirect">
            <p>Перенаправление на страницу оплаты...</p>
        </div>

        <!-- Успешная оплата -->
        <div v-else-if="status === 'success'" class="payment-page__success">
            <h1>Оплата прошла успешно</h1>
            <p>Бронирование подтверждено. Мастер свяжется с вами для уточнения деталей.</p>
            <RouterLink :to="{ name: 'dashboard' }">Перейти в личный кабинет</RouterLink>
        </div>

        <!-- Отмена оплаты -->
        <div v-else-if="status === 'cancel'" class="payment-page__cancel">
            <h1>Оплата отменена</h1>
            <p>Бронирование сохранено. Вы можете попробовать оплатить снова.</p>
            <button type="button" @click="retry">Оплатить снова</button>
            <RouterLink :to="{ name: 'dashboard' }">Вернуться</RouterLink>
        </div>

        <!-- Ошибка инициализации -->
        <div v-else-if="paymentStore.error" class="payment-page__error">
            <p role="alert">{{ paymentStore.error }}</p>
            <button type="button" @click="retry">Попробовать снова</button>
        </div>
    </div>
</template>