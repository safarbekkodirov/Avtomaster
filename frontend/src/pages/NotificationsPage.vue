<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useNotificationStore } from '@/stores/notification.store'
import { useRouter } from 'vue-router'

const store = useNotificationStore()
const router = useRouter()

const hasMore = computed(() =>
    store.pagination !== null &&
    store.pagination.page < store.pagination.totalPages
)

onMounted(() => store.fetchNotifications())

function loadMore(): void {
    if (!store.pagination) return
    store.fetchNotifications(store.pagination.page + 1)
}

function markAllRead(): void {
    store.markAllRead()
}

const typeColors: Record<string, string> = {
    booking_created:   '#f59e0b',
    booking_confirmed: '#3b82f6',
    booking_completed: '#22c55e',
    booking_cancelled: '#ef4444',
    payment_received:  '#8b5cf6',
    review_received:   '#ec4899',
}

function dotColor(type: string): string {
    return typeColors[type] ?? '#9ca3af'
}
</script>

<template>
    <div class="page">
        <header class="page-header">
            <div class="page-header__left">
                <button class="btn-back" @click="router.back()">← Назад</button>
                <h1>Уведомления</h1>
                <span v-if="store.unreadCount > 0" class="badge">{{ store.unreadCount }}</span>
            </div>
            <button
                v-if="store.unreadCount > 0"
                class="btn-ghost"
                @click="markAllRead"
            >
                Прочитать все
            </button>
        </header>

        <div v-if="store.loading && !store.notifications.length" class="loading">
            Загрузка...
        </div>

        <ul v-else-if="store.notifications.length" class="notifications-list">
            <li
                v-for="n in store.notifications"
                :key="n.id"
                class="notification-item"
                :class="{ 'notification-item--unread': !n.isRead }"
            >
                <span class="notification-item__dot" :style="{ background: dotColor(n.type) }" />
                <div class="notification-item__content">
                    <strong class="notification-item__title">{{ n.title }}</strong>
                    <p class="notification-item__body">{{ n.body }}</p>
                    <time class="notification-item__time">
                        {{ new Date(n.createdAt).toLocaleString('ru-RU') }}
                    </time>
                </div>
            </li>
        </ul>

        <p v-else class="empty">Нет уведомлений</p>

        <button v-if="hasMore && !store.loading" class="load-more" @click="loadMore">
            Показать ещё
        </button>
    </div>
</template>

<style scoped>
.page { max-width: 700px; margin: 0 auto; padding: 2rem 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 0.5rem; }
.page-header__left { display: flex; align-items: center; gap: 0.75rem; }
.page-header h1 { margin: 0; font-size: 1.6rem; font-weight: 800; color: #1a1a2e; }
.badge {
    padding: 2px 10px; background: #e63946; color: white; border-radius: 20px;
    font-size: 0.8rem; font-weight: 700;
}
.btn-back {
    padding: 6px 14px; background: #f5f5f5; border: none; border-radius: 8px;
    color: #555; font-size: 0.85rem; cursor: pointer;
}
.btn-back:hover { background: #eee; color: #1a1a2e; }
.btn-ghost {
    padding: 8px 16px; background: none; border: 1.5px solid #e0e0e0; border-radius: 8px;
    color: #666; font-size: 0.85rem; cursor: pointer;
}
.btn-ghost:hover { border-color: #1a1a2e; color: #1a1a2e; }
.loading { text-align: center; padding: 3rem; color: #999; }
.notifications-list { list-style: none; padding: 0; margin: 0; }
.notification-item {
    display: flex; gap: 14px; padding: 1rem 1.25rem;
    border: 1px solid #eee; border-radius: 12px;
    margin-bottom: 0.5rem; transition: all 0.15s;
}
.notification-item--unread { background: #fafafa; border-left: 3px solid #e63946; }
.notification-item__dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 6px;
}
.notification-item__content { flex: 1; }
.notification-item__title { display: block; margin-bottom: 4px; font-size: 0.95rem; color: #1a1a2e; }
.notification-item__body { margin: 0 0 4px; color: #666; font-size: 0.85rem; }
.notification-item__time { font-size: 0.8rem; color: #aaa; }
.empty { text-align: center; padding: 3rem; color: #999; }
.load-more {
    display: block; margin: 1rem auto 0; padding: 10px 24px;
    background: white; border: 2px solid #e63946; color: #e63946;
    border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 600;
}
.load-more:hover { background: #e63946; color: white; }
</style>
