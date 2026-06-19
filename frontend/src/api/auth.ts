import client from './client'
import type { AuthResponse, LoginPayload, RegisterPayload } from '@/types/user.types'

export const authApi = {
  register(payload: RegisterPayload): Promise<AuthResponse> {
    const body: Record<string, any> = {
      email: payload.email,
      password: payload.password,
      firstName: payload.firstName,
      lastName: payload.lastName,
      roles: payload.role === 'master' ? ['ROLE_MASTER'] : ['ROLE_USER'],
    }
    if (payload.phone) body.phone = payload.phone
    if (payload.role === 'master') {
      if ((payload as any).regionName) body.regionName = (payload as any).regionName
      if ((payload as any).address) body.address = (payload as any).address
    }
    return client.post<AuthResponse>('/api/v1/users', body, {
      headers: { Authorization: '' },
    }).then(r => r.data)
  },

  login(payload: LoginPayload): Promise<AuthResponse> {
    return client.post<AuthResponse>('/api/v1/users/auth', payload).then(r => r.data)
  },

  refresh(): Promise<AuthResponse> {
    return client.post<AuthResponse>('/api/v1/users/auth/refreshToken').then(r => r.data)
  },

  logout(): Promise<void> {
    return client.post('/api/v1/auth/logout').then(() => undefined)
  },

  logoutAll(): Promise<void> {
    return client.post('/api/v1/auth/logout-all').then(() => undefined)
  },
}
