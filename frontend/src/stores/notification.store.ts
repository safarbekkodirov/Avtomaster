import { defineStore } from 'pinia'
import { ref } from 'vue'
import { notificationsApi } from '@/api/notifications'
import type { Notification } from '@/types/notification.types'
import type { Pagination } from '@/types/common.types'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref<Notification[]>([])
  const pagination    = ref<Pagination | null>(null)
  const unreadCount   = ref(0)
  const loading       = ref(false)

  async function fetchNotifications(page = 1, perPage = 20): Promise<void> {
    loading.value = true
    try {
      const result        = await notificationsApi.list({ page, perPage })
      notifications.value = page === 1 ? result.data : [...notifications.value, ...result.data]
      pagination.value    = result.pagination
    } finally {
      loading.value = false
    }
  }

  async function fetchUnreadCount(): Promise<void> {
    try {
      const result    = await notificationsApi.unreadCount()
      unreadCount.value = result.count
    } catch {
      // silently ignore
    }
  }

  async function markAllRead(): Promise<void> {
    await notificationsApi.markAllRead()
    unreadCount.value = 0
    notifications.value.forEach(n => { n.isRead = true })
  }

  function reset(): void {
    notifications.value = []
    pagination.value    = null
    unreadCount.value   = 0
  }

  return { notifications, pagination, unreadCount, loading, fetchNotifications, fetchUnreadCount, markAllRead, reset }
})
