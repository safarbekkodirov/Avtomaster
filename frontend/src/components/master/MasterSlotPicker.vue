<!-- src/components/master/MasterSlotPicker.vue -->
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useMasterStore } from '@/stores/master.store'
import type { TimeSlot } from '@/types/master.types'

const props = defineProps<{
    masterId:   number
    serviceId?: number
}>()

const emit = defineEmits<{
    (e: 'select', slot: TimeSlot & { date: string }): void
}>()

const store        = useMasterStore()
const selectedDate = ref<string | null>(null)

// Генерируем диапазон дат — ближайшие 14 дней
const dateRange = computed(() => {
    const dates: string[] = []
    const today = new Date()
    for (let i = 0; i < 14; i++) {
        const d = new Date(today)
        d.setDate(d.getDate() + i)
        dates.push(d.toISOString().slice(0, 10))
    }
    return dates
})

const slotsForDate = computed(() =>
    selectedDate.value ? (store.slots[selectedDate.value] ?? []) : []
)

const datesWithSlots = computed(() =>
    new Set(Object.keys(store.slots))
)

onMounted(async () => {
    await store.fetchSlots(props.masterId, {
        dateFrom:  dateRange.value[0],
        dateTo:    dateRange.value[dateRange.value.length - 1],
        serviceId: props.serviceId,
    })

    // Автовыбор первого дня с доступными слотами
    selectedDate.value = dateRange.value.find(d => datesWithSlots.value.has(d)) ?? null
})

function selectSlot(slot: TimeSlot): void {
    if (!selectedDate.value) return
    emit('select', { ...slot, date: selectedDate.value })
}
</script>

<template>
    <div class="slot-picker">
        <!-- Выбор даты -->
        <div class="slot-picker__dates" role="listbox" aria-label="Выберите дату">
            <button
                v-for="date in dateRange"
                :key="date"
                :disabled="!datesWithSlots.has(date)"
                :aria-selected="selectedDate === date"
                :aria-label="date"
                class="slot-picker__date"
                :class="{
          'slot-picker__date--active':    selectedDate === date,
          'slot-picker__date--available': datesWithSlots.has(date),
        }"
                type="button"
                @click="selectedDate = date"
            >
                {{ new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' }) }}
            </button>
        </div>

        <!-- Слоты выбранного дня -->
        <div v-if="selectedDate" class="slot-picker__slots" role="listbox" aria-label="Выберите время">
            <p v-if="slotsForDate.length === 0" class="slot-picker__empty">
                Нет доступных слотов
            </p>
            <button
                v-for="slot in slotsForDate"
                :key="slot.id"
                class="slot-picker__slot"
                type="button"
                @click="selectSlot(slot)"
            >
                {{ slot.startTime }} — {{ slot.endTime }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.slot-picker { margin: 1rem 0; }
.slot-picker__dates { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; }
.slot-picker__date {
    padding: 8px 14px; border: 1px solid #ddd; border-radius: 8px;
    background: white; cursor: pointer; font-size: 0.85rem; white-space: nowrap;
    transition: all 0.2s;
}
.slot-picker__date:disabled { opacity: 0.4; cursor: default; }
.slot-picker__date--available { border-color: #28a745; }
.slot-picker__date--active { background: #1a1a2e; color: white; border-color: #1a1a2e; }
.slot-picker__slots { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.slot-picker__empty { color: #999; font-size: 0.9rem; }
.slot-picker__slot {
    padding: 10px 18px; border: 2px solid #e0e0e0; border-radius: 8px;
    background: white; cursor: pointer; font-size: 0.95rem; font-weight: 600;
    transition: all 0.2s;
}
.slot-picker__slot:hover { border-color: #e63946; color: #e63946; }
</style>