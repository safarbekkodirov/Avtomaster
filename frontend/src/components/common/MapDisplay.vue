<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps<{
  lat: number
  lng: number
  zoom?: number
  popup?: string
}>()

const mapContainer = ref<HTMLDivElement>()
let map: L.Map | null = null
let marker: L.Marker | null = null

function initMap() {
  if (!mapContainer.value) return

  map = L.map(mapContainer.value).setView([props.lat, props.lng], props.zoom ?? 15)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  }).addTo(map)

  marker = L.marker([props.lat, props.lng]).addTo(map)

  if (props.popup) {
    marker.bindPopup(props.popup).openPopup()
  }
}

watch(() => [props.lat, props.lng], ([lat, lng]) => {
  if (map && marker) {
    map.setView([lat, lng])
    marker.setLatLng([lat, lng])
  }
})

onMounted(() => {
  initMap()
})

onUnmounted(() => {
  map?.remove()
})
</script>

<template>
  <div ref="mapContainer" class="map" />
</template>

<style scoped>
.map {
  width: 100%;
  height: 300px;
  border-radius: 10px;
  border: 1px solid #eee;
  z-index: 0;
}
</style>
