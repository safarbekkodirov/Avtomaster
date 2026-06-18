<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useNotificationStore } from '@/stores/notification.store'

const store = useNotificationStore()

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

const typeIcons: Record<string, string> = {
    booking_created:   '📋',
    booking_confirmed: '✅',
    booking_completed: '🎉',
    booking_cancelled: '❌',
    payment_received:  '💰',
    review_received:   '⭐',
}

function icon(type: string): string {
    return typeIcons[type] ?? '🔔'
}
</script>

<template>
    <div class="notifications-page">
        <header class="notifications-page__header">
            <h1>Уведомления</h1>
            <button
                v-if="store.unreadCount > 0"
                class="btn-mark-read"
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
                <span class="notification-item__icon">{{ icon(n.type) }}</span>
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
.notifications-page { max-width: 700px; margin: 0 auto; padding: 2rem 1rem; }

.notifications-page__header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.5rem;
}
.notifications-page__header h1 { margin: 0; }

.btn-mark-read {
    padding: 6px 16px; background: white; color: #1a1a2e;
    border: 1px solid #ddd; border-radius: 6px; cursor: pointer;
    font-size: 0.85rem; font-weight: 600;
}
.btn-mark-read:hover { border-color: #1a1a2e; }

.loading { text-align: center; padding: 3rem; color: #666; }

.notifications-list { list-style: none; padding: 0; margin: 0; }

.notification-item {
    display: flex; gap: 12px; padding: 1rem;
    border: 1px solid #e5e5e5; border-radius: 8px;
    margin-bottom: 0.5rem; transition: background 0.15s;
}
.notification-item--unread { background: #f8f9fa; border-left: 3px solid #e63946; }

.notification-item__icon { font-size: 1.5rem; flex-shrink: 0; }
.notification-item__content { flex: 1; }
.notification-item__title { display: block; margin-bottom: 4px; }
.notification-item__body { margin: 0 0 4px; color: #666; font-size: 0.9rem; }
.notification-item__time { font-size: 0.8rem; color: #999; }

.empty { text-align: center; padding: 3rem; color: #999; }

.load-more {
    display: block; margin: 1rem auto 0; padding: 8px 20px;
    background: white; border: 2px solid #e63946; color: #e63946;
    border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600;
}
.load-more:hover { background: #e63946; color: white; }
</style>
