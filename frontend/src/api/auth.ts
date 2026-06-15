// src/api/auth.ts

import client from './client'
import type { AuthResponse, LoginPayload, RegisterPayload } from '@/types/user.types'

export const authApi = {
  register(payload: RegisterPayload): Promise<AuthResponse> {
    return client.post<AuthResponse>('/api/v1/auth/register', payload).then(r => r.data)
  },

  login(payload: LoginPayload): Promise<AuthResponse> {
    return client.post<AuthResponse>('/api/v1/auth/login', payload).then(r => r.data)
  },

  refresh(): Promise<AuthResponse> {
    return client.post<AuthResponse>('/api/v1/auth/refresh').then(r => r.data)
  },

  logout(): Promise<void> {
    return client.post('/api/v1/auth/logout').then(() => undefined)
  },

  logoutAll(): Promise<void> {
    return client.post('/api/v1/auth/logout-all').then(() => undefined)
  },
}
