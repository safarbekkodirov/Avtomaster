import client from './client'
import type { Notification, UnreadCountResponse } from '@/types/notification.types'
import type { Pagination } from '@/types/common.types'

export const notificationsApi = {
  list(params?: { page?: number; perPage?: number }): Promise<{ data: Notification[]; pagination: Pagination }> {
    return client
      .get<{ data: Notification[]; pagination: Pagination }>('/api/v1/notifications', { params })
      .then(r => r.data)
  },

  unreadCount(): Promise<UnreadCountResponse> {
    return client
      .get<UnreadCountResponse>('/api/v1/notifications/unread-count')
      .then(r => r.data)
  },

  markAllRead(): Promise<void> {
    return client
      .post('/api/v1/notifications/read-all')
      .then(() => undefined)
  },
}
