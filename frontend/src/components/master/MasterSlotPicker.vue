<!-- src/components/master/MasterSlotPicker.vue -->
<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'select', slot: { date: string; startTime: string; endTime: string }): void
}>()

const selectedDate  = ref('')
const selectedStart = ref('10:00')
const selectedEnd   = ref('12:00')

const dates = (() => {
  const list: string[] = []
  const today = new Date()
  for (let i = 0; i < 14; i++) {
    const d = new Date(today)
    d.setDate(d.getDate() + i)
    list.push(d.toISOString().slice(0, 10))
  }
  return list
})()

function formatDate(d: string): string {
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', weekday: 'short' })
}

function emitSelection(): void {
  if (selectedDate.value && selectedStart.value && selectedEnd.value) {
    emit('select', {
      date:      selectedDate.value,
      startTime: selectedStart.value,
      endTime:   selectedEnd.value,
    })
  }
}

function onDateSelect(d: string): void {
  selectedDate.value = d
  emitSelection()
}

function onTimeChange(): void {
  if (selectedDate.value) emitSelection()
}
</script>

<template>
  <div class="slot-picker">
    <label class="slot-picker__label">Выберите дату</label>
    <div class="slot-picker__dates">
      <button
        v-for="date in dates"
        :key="date"
        type="button"
        class="slot-picker__date"
        :class="{ 'slot-picker__date--active': selectedDate === date }"
        @click="onDateSelect(date)"
      >
        {{ formatDate(date) }}
      </button>
    </div>

    <div v-if="selectedDate" class="slot-picker__time">
      <div class="slot-picker__time-group">
        <label>Начало</label>
        <select v-model="selectedStart" @change="onTimeChange">
          <option v-for="h in 12" :key="h" :value="String(h + 7).padStart(2,'0') + ':00'">
            {{ String(h + 7).padStart(2, '0') }}:00
          </option>
        </select>
      </div>
      <div class="slot-picker__time-group">
        <label>Конец</label>
        <select v-model="selectedEnd" @change="onTimeChange">
          <option v-for="h in 13" :key="h" :value="String(h + 8).padStart(2,'0') + ':00'">
            {{ String(h + 8).padStart(2, '0') }}:00
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

<style scoped>
.slot-picker { margin: 1rem 0; }
.slot-picker__label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }

.slot-picker__dates { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; }
.slot-picker__date {
  padding: 8px 14px; border: 1px solid #ddd; border-radius: 8px;
  background: white; cursor: pointer; font-size: 0.85rem; white-space: nowrap;
  transition: all 0.2s;
}
.slot-picker__date:hover { border-color: #ccc; }
.slot-picker__date--active { background: #1a1a2e; color: white; border-color: #1a1a2e; }

.slot-picker__time { display: flex; gap: 1rem; margin-top: 12px; }
.slot-picker__time-group { display: flex; flex-direction: column; gap: 4px; }
.slot-picker__time-group label { font-size: 0.8rem; font-weight: 600; color: #555; }
.slot-picker__time-group select {
  padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;
  font-size: 0.9rem; background: white;
}
</style>
