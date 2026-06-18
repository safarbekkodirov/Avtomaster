<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { mastersApi } from '@/api/masters'
import type { Master } from '@/types/master.types'

const router    = useRouter()
const authStore = useAuthStore()

const loading   = ref(true)
const error     = ref('')
const myProfile = ref<Master | null>(null)
const isEditing = ref(false)

const form = ref({
  firstName:  '',
  lastName:   '',
  phone:      '',
  bio:        '',
  regionName: '',
  address:    '',
})

const regions = [
  'Бишкек', 'Ош', 'Каракол', 'Токмок',
  'Джалал-Абад', 'Нарын', 'Талас',
]

const showAddService = ref(false)
const serviceForm = ref({
  name:             '',
  price:            0,
  durationMinutes:  30,
  categoryName:     '',
})

const editingServiceId = ref<number | null>(null)

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

function startCreate() {
  isEditing.value = true
  form.value = {
    firstName:  authStore.user?.firstName ?? '',
    lastName:   authStore.user?.lastName  ?? '',
    phone:      '',
    bio:        '',
    regionName: 'Бишкек',
    address:    '',
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
  }
  isEditing.value = true
}

function startAddService() {
  editingServiceId.value = null
  serviceForm.value = {
    name:            '',
    price:           0,
    durationMinutes: 30,
    categoryName:    '',
  }
  showAddService.value = true
}

function editService(service: { id: number; name: string; price: number; durationMinutes: number; categoryName: string | null }) {
  editingServiceId.value = service.id
  serviceForm.value = {
    name:            service.name,
    price:           service.price,
    durationMinutes: service.durationMinutes,
    categoryName:    service.categoryName ?? '',
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
      categoryName:    serviceForm.value.categoryName || undefined,
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

onMounted(loadProfile)
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
              <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
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
              <span v-if="service.categoryName" class="service-item__category">{{ service.categoryName }}</span>
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
              <input v-model="serviceForm.categoryName" placeholder="Категория" />
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-primary" @click="saveService">Сохранить</button>
            <button class="btn-secondary" @click="cancelService">Отмена</button>
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
</style>
