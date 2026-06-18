<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useNotificationStore } from '@/stores/notification.store'

const store  = useNotificationStore()
const showDropdown = ref(false)

const hasUnread = computed(() => store.unreadCount > 0)
const label     = computed(() =>
    store.unreadCount > 0
        ? `${store.unreadCount} непрочитанных`
        : 'Уведомления'
)

onMounted(() => {
    store.fetchUnreadCount()
    setInterval(() => store.fetchUnreadCount(), 60_000)
})

function toggle(): void {
    showDropdown.value = !showDropdown.value
    if (showDropdown.value && store.notifications.length === 0) {
        store.fetchNotifications()
    }
}

function close(): void {
    showDropdown.value = false
}

function onMarkAllRead(): void {
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
    <div class="bell" v-click-outside="close">
        <button
            class="bell__btn"
            :class="{ 'bell__btn--unread': hasUnread }"
            :aria-label="label"
            @click="toggle"
        >
            🔔
            <span v-if="hasUnread" class="bell__badge">{{ store.unreadCount }}</span>
        </button>

        <div v-if="showDropdown" class="bell__dropdown">
            <div class="bell__header">
                <h3>Уведомления</h3>
                <button
                    v-if="store.unreadCount > 0"
                    class="bell__mark-read"
                    @click="onMarkAllRead"
                >
                    Прочитать все
                </button>
            </div>

            <div v-if="store.loading" class="bell__loading">Загрузка...</div>

            <ul v-else-if="store.notifications.length" class="bell__list">
                <li
                    v-for="n in store.notifications"
                    :key="n.id"
                    class="bell__item"
                    :class="{ 'bell__item--unread': !n.isRead }"
                >
                    <span class="bell__icon">{{ icon(n.type) }}</span>
                    <div class="bell__content">
                        <strong class="bell__title">{{ n.title }}</strong>
                        <p class="bell__body">{{ n.body }}</p>
                        <time class="bell__time">{{ new Date(n.createdAt).toLocaleDateString('ru-RU') }}</time>
                    </div>
                </li>
            </ul>

            <p v-else class="bell__empty">Нет уведомлений</p>
        </div>
    </div>
</template>

<script lang="ts">
export default {
    directives: {
        clickOutside: {
            mounted(el: HTMLElement, binding: { value: () => void }) {
                const handler = (e: MouseEvent) => {
                    if (!el.contains(e.target as Node)) binding.value()
                }
                document.addEventListener('click', handler)
                el._clickOutsideHandler = handler
            },
            unmounted(el: HTMLElement) {
                document.removeEventListener('click', el._clickOutsideHandler)
            }
        }
    }
}
</script>

<style scoped>
.bell { position: relative; }

.bell__btn {
    background: none; border: none; cursor: pointer;
    font-size: 1.3rem; position: relative; padding: 4px;
}
.bell__btn--unread { animation: pulse 2s infinite; }

.bell__badge {
    position: absolute; top: -2px; right: -4px;
    background: #e63946; color: white; border-radius: 50%;
    font-size: 0.65rem; font-weight: 700; min-width: 18px;
    height: 18px; display: flex; align-items: center; justify-content: center;
}

.bell__dropdown {
    position: absolute; top: 100%; right: 0; width: 340px;
    background: white; border: 1px solid #e5e5e5; border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000;
    max-height: 400px; overflow-y: auto;
}

.bell__header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem; border-bottom: 1px solid #eee;
}
.bell__header h3 { margin: 0; font-size: 1rem; }
.bell__mark-read {
    background: none; border: none; color: #1a1a2e;
    font-size: 0.8rem; cursor: pointer; font-weight: 600;
}

.bell__loading { text-align: center; padding: 2rem; color: #999; }

.bell__list { list-style: none; padding: 0; margin: 0; }

.bell__item {
    display: flex; gap: 10px; padding: 0.75rem 1rem;
    border-bottom: 1px solid #f5f5f5; transition: background 0.15s;
}
.bell__item:last-child { border-bottom: none; }
.bell__item--unread { background: #f8f9fa; }
.bell__item:hover { background: #f0f0f0; }

.bell__icon { font-size: 1.2rem; flex-shrink: 0; }

.bell__content { flex: 1; min-width: 0; }
.bell__title { display: block; font-size: 0.85rem; margin-bottom: 2px; }
.bell__body {
    margin: 0; font-size: 0.8rem; color: #666;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.bell__time { font-size: 0.7rem; color: #999; }

.bell__empty { text-align: center; padding: 2rem; color: #999; }

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>
