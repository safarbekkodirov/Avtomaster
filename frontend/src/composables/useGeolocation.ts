// src/composables/useGeolocation.ts

import { ref } from 'vue'

interface Coords {
    lat: number
    lng: number
}

export function useGeolocation() {
    const coords  = ref<Coords | null>(null)
    const loading = ref(false)
    const error   = ref<string | null>(null)

    function detect(): Promise<Coords> {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                error.value = 'Геолокация не поддерживается браузером'
                reject(new Error(error.value))
                return
            }

            loading.value = true

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    coords.value = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    }
                    loading.value = false
                    resolve(coords.value)
                },
                (err) => {
                    error.value   = 'Не удалось определить геолокацию'
                    loading.value = false
                    reject(err)
                },
                { timeout: 8_000, maximumAge: 60_000 }
            )
        })
    }

    return { coords, loading, error, detect }
}