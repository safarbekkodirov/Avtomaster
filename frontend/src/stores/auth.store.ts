import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import type { AuthUser, LoginPayload, RegisterPayload } from '@/types/user.types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('access_token'))
  const user  = ref<AuthUser | null>(null)

  const isAuth   = computed(() => token.value !== null)
  const isMaster = computed(() => user.value?.roles.includes('ROLE_MASTER') ?? false)

  async function login(payload: LoginPayload) {
    const result = await authApi.login(payload)
    token.value  = result.accessToken
    user.value   = result.user
    localStorage.setItem('access_token', result.accessToken)
  }

  async function register(payload: RegisterPayload) {
    const result = await authApi.register(payload)
    token.value  = result.accessToken
    user.value   = result.user
    localStorage.setItem('access_token', result.accessToken)
  }

  async function logout() {
    try { await authApi.logout() } finally { $reset() }
  }

  function $reset() {
    token.value = null
    user.value  = null
    localStorage.removeItem('access_token')
  }

  return { token, user, isAuth, isMaster, login, register, logout, $reset }
})
