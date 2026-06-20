<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { mastersApi } from '@/api/masters'
import { bookingsApi } from '@/api/bookings'
import LocationPicker from '@/components/common/LocationPicker.vue'
import type { Master, ServiceCategory, Region } from '@/types/master.types'
import type { Booking, BookingStatus } from '@/types/booking.types'

const router    = useRouter()
const authStore = useAuthStore()

const loading   = ref(true)
const error     = ref('')
const myProfile = ref<Master | null>(null)
const isEditing = ref(false)
const categories = ref<ServiceCategory[]>([])
const regions    = ref<Region[]>([])

const form = ref({
  firstName:  '',
  lastName:   '',
  phone:      '',
  bio:        '',
  regionName: '',
  address:    '',
  location:   null as { lat: number; lng: number } | null,
})

const showAddService = ref(false)
const serviceForm = ref({
  name:             '',
  price:            0,
  durationMinutes:  30,
  categoryId:       null as number | null,
})

const editingServiceId = ref<number | null>(null)

const bookings = ref<Booking[]>([])
const bookingFilter = ref<BookingStatus | ''>('')
const bookingsLoading = ref(false)

async function loadBookings() {
  bookingsLoading.value = true
  try {
    const result = await bookingsApi.listMaster(bookingFilter.value as BookingStatus || undefined)
    bookings.value = result.data
  } catch {
    bookings.value = []
  } finally {
    bookingsLoading.value = false
  }
}

async function confirmBooking(id: number) {
  try {
    await bookingsApi.confirm(id)
    await loadBookings()
  } catch {
    error.value = 'Ошибка подтверждения'
  }
}

async function completeBooking(id: number) {
  try {
    await bookingsApi.complete(id)
    await loadBookings()
  } catch {
    error.value = 'Ошибка завершения'
  }
}

async function cancelBooking(id: number) {
  if (!confirm('Отменить запись?')) return
  try {
    await bookingsApi.cancel(id)
    await loadBookings()
  } catch {
    error.value = 'Ошибка отмены'
  }
}

const statusLabels: Record<string, string> = {
  pending: 'Ожидает',
  confirmed: 'Подтверждена',
  completed: 'Завершена',
  cancelled: 'Отменена',
}

async function loadProfile() {
  loading.value = true
  try {
    myProfile.value = await mastersApi.getMyProfile()
  } catch {
    myProfile.value = null
  } finally {
    loading.value = false
  }
}

async function loadCategories() {
  try {
    const result = await mastersApi.getCategories()
    categories.value = result.member
  } catch {
    categories.value = []
  }
}

async function loadRegions() {
  try {
    const result = await mastersApi.getRegions()
    regions.value = result.member
  } catch {
    regions.value = []
  }
}

function startCreate() {
  isEditing.value = true
  form.value = {
    firstName:  authStore.user?.firstName ?? '',
    lastName:   authStore.user?.lastName  ?? '',
    phone:      '',
    bio:        '',
    regionName: '',
    address:    '',
    location:   null,
  }
}

function cancelEdit() {
  isEditing.value = false
  form.value = {
    firstName:  '',
    lastName:   '',
    phone:      '',
    bio:        '',
    regionName: '',
    address:    '',
    location:   null,
  }
}

async function saveProfile() {
  error.value = ''
  try {
    const payload = {
      firstName:  form.value.firstName,
      lastName:   form.value.lastName,
      phone:      form.value.phone || undefined,
      bio:        form.value.bio   || undefined,
      regionName: form.value.regionName,
      address:    form.value.address || undefined,
      lat:        form.value.location?.lat ?? undefined,
      lng:        form.value.location?.lng ?? undefined,
    }

    if (myProfile.value) {
      myProfile.value = await mastersApi.update(myProfile.value.id, payload)
    } else {
      myProfile.value = await mastersApi.create(payload)
    }
    isEditing.value = false
  } catch {
    error.value = 'Ошибка сохранения профиля'
  }
}

function startEditProfile() {
  if (!myProfile.value) return
  form.value = {
    firstName:  myProfile.value.firstName,
    lastName:   myProfile.value.lastName,
    phone:      myProfile.value.phone  ?? '',
    bio:        myProfile.value.bio    ?? '',
    regionName: myProfile.value.regionName,
    address:    myProfile.value.address ?? '',
    location:   myProfile.value.lat && myProfile.value.lng
      ? { lat: Number(myProfile.value.lat), lng: Number(myProfile.value.lng) }
      : null,
  }
  isEditing.value = true
}

function startAddService() {
  editingServiceId.value = null
  serviceForm.value = {
    name:            '',
    price:           0,
    durationMinutes: 30,
    categoryId:      null,
  }
  showAddService.value = true
}

function editService(service: { id: number; name: string; price: number; durationMinutes: number; category: { id: number } | null }) {
  editingServiceId.value = service.id
  serviceForm.value = {
    name:            service.name,
    price:           service.price,
    durationMinutes: service.durationMinutes,
    categoryId:      service.category?.id ?? null,
  }
  showAddService.value = true
}

function cancelService() {
  showAddService.value = false
  editingServiceId.value = null
}

async function saveService() {
  if (!myProfile.value) return
  error.value = ''
  try {
    await mastersApi.addService(myProfile.value.id, {
      name:            serviceForm.value.name,
      price:           serviceForm.value.price,
      durationMinutes: serviceForm.value.durationMinutes,
      categoryId:      serviceForm.value.categoryId ?? undefined,
    })
    await loadProfile()
    showAddService.value = false
    editingServiceId.value = null
  } catch {
    error.value = 'Ошибка сохранения услуги'
  }
}

async function deleteService(serviceId: number) {
  if (!myProfile.value) return
  if (!confirm('Удалить услугу?')) return
  try {
    await mastersApi.deleteService(myProfile.value.id, serviceId)
    await loadProfile()
  } catch {
    error.value = 'Ошибка удаления услуги'
  }
}

onMounted(() => {
  loadProfile()
  loadCategories()
  loadRegions()
  loadBookings()
})
</script>

<template>
  <div class="master-dashboard">
    <h1>Кабинет мастера</h1>

    <div v-if="loading" class="loading">Загрузка...</div>

    <div v-else-if="error" class="error-msg">{{ error }}</div>

    <!-- Профиль не создан -->
    <template v-else-if="!myProfile && !isEditing">
      <div class="empty-state">
        <p>У вас ещё нет профиля мастера</p>
        <button class="btn-primary" @click="startCreate">Создать профиль</button>
      </div>
    </template>

    <!-- Форма создания/редактирования -->
    <template v-else-if="isEditing">
      <div class="form-card">
        <h2>{{ myProfile ? 'Редактирование профиля' : 'Новый профиль' }}</h2>
        <div class="form-grid">
          <div class="form-group">
            <label>Имя *</label>
            <input v-model="form.firstName" placeholder="Имя" />
          </div>
          <div class="form-group">
            <label>Фамилия *</label>
            <input v-model="form.lastName" placeholder="Фамилия" />
          </div>
          <div class="form-group">
            <label>Телефон</label>
            <input v-model="form.phone" placeholder="+996..." />
          </div>
          <div class="form-group">
            <label>Регион *</label>
            <select v-model="form.regionName">
              <option value="">Выберите...</option>
              <option v-for="r in regions" :key="r.id" :value="r.name">{{ r.name }}</option>
            </select>
          </div>
          <div class="form-group full-width">
            <label>Адрес</label>
            <input v-model="form.address" placeholder="Улица, дом..." />
          </div>
          <div class="form-group full-width">
            <label>О себе</label>
            <textarea v-model="form.bio" rows="3" placeholder="Расскажите о себе..." />
          </div>
        </div>

        <div class="form-group full-width" style="margin-top:1rem">
          <label>📍 Местоположение на карте</label>
          <LocationPicker v-model="form.location" />
        </div>

        <div class="form-actions">
          <button class="btn-primary" @click="saveProfile">Сохранить</button>
          <button class="btn-secondary" @click="cancelEdit">Отмена</button>
        </div>
      </div>
    </template>

    <!-- Профиль мастера -->
    <template v-else-if="myProfile">
      <div class="profile-card">
        <div class="profile-card__header">
          <div class="profile-card__info">
            <h2>
              {{ myProfile.firstName }} {{ myProfile.lastName }}
              <span v-if="myProfile.isVerified" class="verified">✓ Верифицирован</span>
            </h2>
            <p class="region">{{ myProfile.regionName }}</p>
            <p v-if="myProfile.address" class="address">{{ myProfile.address }}</p>
            <p v-if="myProfile.phone" class="phone">{{ myProfile.phone }}</p>
          </div>
          <div class="profile-card__stats">
            <span class="rating">★ {{ Number(myProfile.rating).toFixed(1) }}</span>
            <span class="reviews">({{ myProfile.reviewsCount }})</span>
          </div>
        </div>
        <p v-if="myProfile.bio" class="bio">{{ myProfile.bio }}</p>
        <button class="btn-secondary" @click="startEditProfile">Редактировать профиль</button>
      </div>

      <!-- Услуги -->
      <div class="services-section">
        <div class="services-header">
          <h3>Услуги</h3>
          <button class="btn-add" @click="startAddService">+ Добавить</button>
        </div>

        <div v-if="!myProfile.services.length" class="empty-services">
          Услуг пока нет
        </div>

        <div v-else class="services-list">
          <div
            v-for="service in myProfile.services"
            :key="service.id"
            class="service-item"
          >
            <div class="service-item__info">
              <span class="service-item__name">{{ service.name }}</span>
              <span class="service-item__duration">{{ service.durationMinutes }} мин</span>
              <span v-if="service.category" class="service-item__category">{{ service.category.name }}</span>
            </div>
            <div class="service-item__right">
              <span class="service-item__price">{{ service.price.toLocaleString('ru-RU') }} ₽</span>
              <button class="btn-icon" @click="editService(service)" title="Редактировать">✏️</button>
              <button class="btn-icon btn-icon--danger" @click="deleteService(service.id)" title="Удалить">🗑️</button>
            </div>
          </div>
        </div>

        <!-- Форма добавления/редактирования услуги -->
        <div v-if="showAddService" class="service-form">
          <h4>{{ editingServiceId ? 'Редактировать услугу' : 'Новая услуга' }}</h4>
          <div class="form-grid">
            <div class="form-group">
              <label>Название *</label>
              <input v-model="serviceForm.name" placeholder="Название услуги" />
            </div>
            <div class="form-group">
              <label>Цена (₽) *</label>
              <input v-model.number="serviceForm.price" type="number" min="0" />
            </div>
            <div class="form-group">
              <label>Длительность (мин) *</label>
              <input v-model.number="serviceForm.durationMinutes" type="number" min="5" step="5" />
            </div>
            <div class="form-group">
              <label>Категория</label>
              <select v-model="serviceForm.categoryId">
                <option :value="null">Без категории</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-primary" @click="saveService">Сохранить</button>
            <button class="btn-secondary" @click="cancelService">Отмена</button>
          </div>
        </div>
      </div>
    </template>

    <!-- Записи клиентов -->
    <template v-if="myProfile && !isEditing">
      <div class="card">
        <div class="card__header">
          <h3 class="card__title">Записи клиентов</h3>
          <select v-model="bookingFilter" class="filter-select" @change="loadBookings">
            <option value="">Все</option>
            <option value="pending">Ожидает</option>
            <option value="confirmed">Подтверждена</option>
            <option value="completed">Завершена</option>
            <option value="cancelled">Отменена</option>
          </select>
        </div>

        <div v-if="bookingsLoading" class="loading-small">Загрузка...</div>

        <div v-else-if="!bookings.length" class="empty-text">Записей нет</div>

        <div v-else class="bookings-list">
          <div v-for="b in bookings" :key="b.id" class="booking-item">
            <div class="booking-item__header">
              <span class="booking-item__client">{{ b.clientFirstName }} {{ b.clientLastName }}</span>
              <span class="status-badge" :class="`status-badge--${b.status}`">{{ statusLabels[b.status] }}</span>
            </div>
            <div class="booking-item__details">
              <span class="booking-item__service">{{ b.serviceName }}</span>
              <span class="booking-item__time">{{ b.slotDate }} · {{ b.slotStartTime }} — {{ b.slotEndTime }}</span>
              <span class="booking-item__price">{{ Number(b.total).toLocaleString('ru-RU') }} ₽</span>
            </div>
            <div v-if="b.notes" class="booking-item__notes">{{ b.notes }}</div>
            <div class="booking-item__actions" v-if="b.status === 'pending'">
              <button class="btn-sm btn-confirm" @click="confirmBooking(b.id)">Подтвердить</button>
              <button class="btn-sm btn-cancel" @click="cancelBooking(b.id)">Отменить</button>
            </div>
            <div class="booking-item__actions" v-else-if="b.status === 'confirmed'">
              <button class="btn-sm btn-complete" @click="completeBooking(b.id)">Завершить</button>
              <button class="btn-sm btn-cancel" @click="cancelBooking(b.id)">Отменить</button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.master-dashboard {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

h1 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  color: #1a1a2e;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.error-msg {
  color: #e63946;
  text-align: center;
  padding: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.empty-state p {
  color: #666;
  margin-bottom: 1rem;
}

.form-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.form-card h2 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.3rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #555;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.95rem;
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-primary {
  padding: 10px 24px;
  background: #e63946;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.95rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-primary:hover {
  background: #d32f3f;
}

.btn-secondary {
  padding: 10px 24px;
  background: white;
  color: #666;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.95rem;
  cursor: pointer;
}

.btn-secondary:hover {
  border-color: #999;
}

.profile-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

.profile-card__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.profile-card__info h2 {
  margin: 0 0 0.25rem;
}

.verified {
  color: #28a745;
  font-size: 0.8rem;
  font-weight: normal;
}

.region, .address, .phone {
  margin: 0.15rem 0;
  color: #666;
  font-size: 0.9rem;
}

.profile-card__stats {
  text-align: right;
}

.rating {
  font-size: 1.2rem;
  font-weight: 700;
  color: #f59e0b;
}

.reviews {
  color: #666;
  font-size: 0.85rem;
}

.bio {
  color: #444;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.services-section {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.services-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.services-header h3 {
  margin: 0;
}

.btn-add {
  padding: 6px 16px;
  background: #1a1a2e;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-add:hover {
  background: #2d2d4e;
}

.empty-services {
  color: #999;
  text-align: center;
  padding: 2rem;
}

.services-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.service-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border: 1px solid #eee;
  border-radius: 8px;
}

.service-item__info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.service-item__name {
  font-weight: 600;
}

.service-item__duration {
  color: #888;
  font-size: 0.85rem;
}

.service-item__category {
  color: #999;
  font-size: 0.8rem;
  padding: 2px 8px;
  background: #f0f0f0;
  border-radius: 4px;
}

.service-item__right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.service-item__price {
  font-weight: 700;
  color: #e63946;
  margin-right: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 4px;
}

.btn-icon--danger {
  opacity: 0.6;
}

.btn-icon--danger:hover {
  opacity: 1;
}

.service-form {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
}

.service-form h4 {
  margin: 0 0 1rem;
}

.card {
  background: white;
  border-radius: 14px;
  padding: 1.5rem;
  border: 1px solid #eee;
  margin-top: 1.5rem;
}

.card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card__title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
}

.filter-select {
  padding: 6px 12px;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.85rem;
  background: white;
}

.loading-small {
  text-align: center;
  padding: 1.5rem;
  color: #999;
}

.empty-text {
  text-align: center;
  padding: 1.5rem;
  color: #aaa;
}

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.booking-item {
  background: #f9f9f9;
  border-radius: 12px;
  padding: 1rem 1.25rem;
}

.booking-item__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.booking-item__client {
  font-weight: 700;
  color: #1a1a2e;
}

.status-badge {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge--pending   { background: #fff8e1; color: #f57f17; }
.status-badge--confirmed { background: #e3f2fd; color: #1565c0; }
.status-badge--completed { background: #e8f5e9; color: #2e7d32; }
.status-badge--cancelled { background: #fce4ec; color: #c62828; }

.booking-item__details {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
  font-size: 0.9rem;
  color: #666;
}

.booking-item__service {
  font-weight: 600;
  color: #333;
}

.booking-item__price {
  font-weight: 700;
  color: #e63946;
}

.booking-item__notes {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #888;
  font-style: italic;
}

.booking-item__actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.btn-sm {
  padding: 6px 16px;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-confirm { background: #e3f2fd; color: #1565c0; }
.btn-confirm:hover { background: #bbdefb; }
.btn-complete { background: #e8f5e9; color: #2e7d32; }
.btn-complete:hover { background: #c8e6c9; }
.btn-cancel { background: #fce4ec; color: #c62828; }
.btn-cancel:hover { background: #f8bbd0; }
</style>
