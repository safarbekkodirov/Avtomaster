<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps<{
  modelValue: { lat: number; lng: number } | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: { lat: number; lng: number }]
}>()

const mapContainer = ref<HTMLDivElement>()
const addressQuery = ref('')
const searching = ref(false)
let map: L.Map | null = null
let marker: L.Marker | null = null

const defaultCoords: [number, number] = [42.8746, 74.5698]

function initMap() {
  if (!mapContainer.value) return

  const center = props.modelValue
    ? [props.modelValue.lat, props.modelValue.lng]
    : defaultCoords

  map = L.map(mapContainer.value).setView(center as L.LatLngTuple, 13)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  }).addTo(map)

  if (props.modelValue) {
    marker = L.marker(center as L.LatLngTuple).addTo(map)
  }

  map.on('click', (e: L.LeafletMouseEvent) => {
    setMarker(e.latlng.lat, e.latlng.lng)
  })
}

function setMarker(lat: number, lng: number) {
  if (!map) return

  if (marker) {
    marker.setLatLng([lat, lng])
  } else {
    marker = L.marker([lat, lng]).addTo(map)
  }

  emit('update:modelValue', { lat, lng })
}

async function searchAddress() {
  if (!addressQuery.value.trim()) return
  searching.value = true
  try {
    const query = encodeURIComponent(addressQuery.value.trim())
    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=1`)
    const data = await res.json()
    if (data.length > 0) {
      const { lat, lon } = data[0]
      setMarker(parseFloat(lat), parseFloat(lon))
      map?.setView([parseFloat(lat), parseFloat(lon)], 15)
    }
  } catch {
    // ignore
  } finally {
    searching.value = false
  }
}

function useMyLocation() {
  if (!navigator.geolocation) return
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      setMarker(pos.coords.latitude, pos.coords.longitude)
      map?.setView([pos.coords.latitude, pos.coords.longitude], 15)
    },
    () => {}
  )
}

watch(() => props.modelValue, (val) => {
  if (val && map) {
    map.setView([val.lat, val.lng], 15)
    if (marker) {
      marker.setLatLng([val.lat, val.lng])
    }
  }
})

onMounted(initMap)
onUnmounted(() => { map?.remove() })
</script>

<template>
  <div class="location-picker">
    <div class="location-search">
      <input
        v-model="addressQuery"
        placeholder="Поиск адреса..."
        @keyup.enter="searchAddress"
      />
      <button @click="searchAddress" :disabled="searching">
        {{ searching ? '...' : '🔍' }}
      </button>
      <button class="btn-geo" @click="useMyLocation" title="Менин жериим">
        📍
      </button>
    </div>
    <div ref="mapContainer" class="map" />
    <p v-if="modelValue" class="coords">
      📌 {{ modelValue.lat.toFixed(6) }}, {{ modelValue.lng.toFixed(6) }}
    </p>
  </div>
</template>

<style scoped>
.location-picker {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.location-search {
  display: flex;
  gap: 8px;
}

.location-search input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.95rem;
}

.location-search button {
  padding: 10px 14px;
  background: #1a1a2e;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.location-search button:disabled {
  opacity: 0.5;
}

.btn-geo {
  background: #e63946 !important;
}

.map {
  width: 100%;
  height: 250px;
  border-radius: 10px;
  border: 1px solid #eee;
  z-index: 0;
}

.coords {
  font-size: 0.8rem;
  color: #888;
  text-align: center;
  margin: 0;
}
</style>
