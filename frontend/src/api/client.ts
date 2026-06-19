import axios, { type AxiosInstance, type AxiosError, type InternalAxiosRequestConfig } from 'axios'

const client: AxiosInstance = axios.create({
  baseURL:         import.meta.env.VITE_API_URL || '',
  timeout:         15_000,
  withCredentials: true,
  headers:         { 'Content-Type': 'application/json' },
})

let isRefreshing = false
let refreshQueue: Array<(token: string) => void> = []

function processQueue(newToken: string): void {
  refreshQueue.forEach(cb => cb(newToken))
  refreshQueue = []
}

client.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  if (config.method === 'patch') {
    config.headers['Content-Type'] = 'application/merge-patch+json'
  }
  return config
})

client.interceptors.response.use(
  response => response,
  async (error: AxiosError) => {
    const originalConfig = error.config as InternalAxiosRequestConfig & { _retry?: boolean }
    if (error.response?.status !== 401 || originalConfig._retry) {
      return Promise.reject(error)
    }
    if (isRefreshing) {
      return new Promise(resolve => {
        refreshQueue.push((token: string) => {
          originalConfig.headers.Authorization = `Bearer ${token}`
          resolve(client.request(originalConfig))
        })
      })
    }
    originalConfig._retry = true
    isRefreshing = true
    try {
      const res = await client.post<{ accessToken: string }>('/api/v1/auth/refresh')
      const newToken = res.data.accessToken
      localStorage.setItem('access_token', newToken)
      processQueue(newToken)
      originalConfig.headers.Authorization = `Bearer ${newToken}`
      return client.request(originalConfig)
    } catch {
      localStorage.removeItem('access_token')
      window.location.href = '/login'
      return Promise.reject(error)
    } finally {
      isRefreshing = false
    }
  }
)

export default client
