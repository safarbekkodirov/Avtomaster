<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { adminApi, type AdminUser, type AdminMaster } from '@/api/admin'

const router    = useRouter()
const authStore = useAuthStore()

const activeTab = ref<'users' | 'masters'>('users')
const users     = ref<AdminUser[]>([])
const masters   = ref<AdminMaster[]>([])
const regions   = ref<{ id: number; name: string }[]>([])
const loading   = ref(true)
const error     = ref('')
const expandedMasterId = ref<number | null>(null)

const showForm  = ref(false)
const saving    = ref(false)
const editingMasterId = ref<number | null>(null)
const form = ref({
  email:      '',
  password:   '',
  firstName:  '',
  lastName:   '',
  phone:      '',
  bio:        '',
  regionName: '',
  address:    '',
})

const roleLabels: Record<string, string> = {
  ROLE_USER:   'Клиент',
  ROLE_MASTER: 'Мастер',
  ROLE_ADMIN:  'Админ',
}

async function loadUsers() {
  loading.value = true
  error.value   = ''
  try {
    const result = await adminApi.getUsers()
    users.value  = result.member ?? []
  } catch {
    error.value = 'Ошибка загрузки пользователей'
  } finally {
    loading.value = false
  }
}

async function loadMasters() {
  loading.value = true
  error.value   = ''
  try {
    const result = await adminApi.getMasters()
    masters.value = result.member ?? []
  } catch {
    error.value = 'Ошибка загрузки мастеров'
  } finally {
    loading.value = false
  }
}

async function loadRegions() {
  try {
    const result = await adminApi.getRegions()
    regions.value = result.member ?? []
  } catch {
    regions.value = []
  }
}

async function loadAll() {
  if (activeTab.value === 'users') await loadUsers()
  else await loadMasters()
}

async function deleteUser(id: number) {
  if (!confirm('Удалить пользователя?')) return
  try {
    await adminApi.deleteUser(id)
    await loadUsers()
  } catch {
    error.value = 'Ошибка удаления пользователя'
  }
}

async function deleteMaster(id: number) {
  if (!confirm('Удалить мастера?')) return
  try {
    await adminApi.deleteMaster(id)
    await loadMasters()
  } catch {
    error.value = 'Ошибка удаления мастера'
  }
}

function openForm() {
  editingMasterId.value = null
  form.value = {
    email:      '',
    password:   '',
    firstName:  '',
    lastName:   '',
    phone:      '',
    bio:        '',
    regionName: '',
    address:    '',
  }
  showForm.value = true
}

function startEdit(master: AdminMaster) {
  editingMasterId.value = master.id
  form.value = {
    email:      '',
    password:   '',
    firstName:  master.firstName,
    lastName:   master.lastName,
    phone:      master.phone ?? '',
    bio:        master.bio ?? '',
    regionName: master.regionName,
    address:    master.address ?? '',
  }
  showForm.value = true
}

function cancelForm() {
  showForm.value = false
  editingMasterId.value = null
}

async function saveMaster() {
  error.value   = ''
  saving.value  = true
  try {
    if (editingMasterId.value) {
      await adminApi.updateMaster(editingMasterId.value, {
        firstName:  form.value.firstName,
        lastName:   form.value.lastName,
        phone:      form.value.phone || undefined,
        bio:        form.value.bio || undefined,
        regionName: form.value.regionName,
        address:    form.value.address || undefined,
      })
    } else {
      await adminApi.createMaster({
        email:      form.value.email,
        password:   form.value.password,
        firstName:  form.value.firstName,
        lastName:   form.value.lastName,
        phone:      form.value.phone || undefined,
        regionName: form.value.regionName,
        address:    form.value.address || undefined,
      })
    }
    showForm.value = false
    editingMasterId.value = null
    await loadMasters()
  } catch {
    error.value = editingMasterId.value ? 'Ошибка обновления мастера' : 'Ошибка создания мастера'
  } finally {
    saving.value = false
  }
}

function switchTab(tab: 'users' | 'masters') {
  activeTab.value = tab
  loadAll()
}

function logout() {
  authStore.logout()
  router.push({ name: 'login' })
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('ru-RU')
}

function toggleMaster(masterId: number) {
  expandedMasterId.value = expandedMasterId.value === masterId ? null : masterId
}

onMounted(() => {
  loadAll()
  loadRegions()
})
</script>

<template>
  <div class="admin-page">
    <header class="page-header">
      <div class="page-header__left">
        <h1>Админ панель</h1>
        <RouterLink to="/admin/categories" class="nav-link">Категории</RouterLink>
      </div>
      <div class="page-header__right">
        <span class="user-name">{{ authStore.user?.email }}</span>
        <button class="btn-logout" @click="logout">Выйти</button>
      </div>
    </header>

    <div class="tabs">
      <button
        class="tab"
        :class="{ 'tab--active': activeTab === 'users' }"
        @click="switchTab('users')"
      >
        Клиенты ({{ users.length }})
      </button>
      <button
        class="tab"
        :class="{ 'tab--active': activeTab === 'masters' }"
        @click="switchTab('masters')"
      >
        Мастера ({{ masters.length }})
      </button>
    </div>

    <div v-if="error" class="error-msg">{{ error }}</div>
    <div v-if="loading" class="loading">Загрузка...</div>

    <!-- Users tab -->
    <template v-if="!loading && activeTab === 'users'">
      <div v-if="!users.length" class="empty">Пользователей нет</div>
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Имя</th>
              <th>Фамилия</th>
              <th>Роль</th>
              <th>Дата регистрации</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id">
              <td>{{ u.id }}</td>
              <td>{{ u.email }}</td>
              <td>{{ u.firstName || '—' }}</td>
              <td>{{ u.lastName || '—' }}</td>
              <td>
                <span
                  v-for="role in u.roles"
                  :key="role"
                  class="role-badge"
                  :class="{
                    'role-badge--admin':  role === 'ROLE_ADMIN',
                    'role-badge--master': role === 'ROLE_MASTER',
                    'role-badge--user':   role === 'ROLE_USER',
                  }"
                >
                  {{ roleLabels[role] || role }}
                </span>
              </td>
              <td>{{ u.createdAt ? formatDate(u.createdAt) : '—' }}</td>
              <td>
                <button class="btn-icon btn-icon--danger" @click="deleteUser(u.id)" title="Удалить">
                  Удалить
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- Masters tab -->
    <template v-if="!loading && activeTab === 'masters'">
      <div class="tab-actions">
        <button class="btn-primary" @click="openForm">+ Добавить мастера</button>
      </div>

      <!-- Form -->
      <div v-if="showForm" class="form-card">
        <h2>{{ editingMasterId ? 'Редактирование' : 'Новый мастер' }}</h2>
        <div class="form-grid">
          <template v-if="!editingMasterId">
            <div class="form-group">
              <label>Email *</label>
              <input v-model="form.email" type="email" placeholder="email@example.com" />
            </div>
            <div class="form-group">
              <label>Пароль *</label>
              <input v-model="form.password" type="password" placeholder="Минимум 6 символ" />
            </div>
          </template>
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
        <div class="form-actions">
          <button class="btn-primary" @click="saveMaster" :disabled="saving">
            {{ saving ? 'Сохранение...' : 'Сохранить' }}
          </button>
          <button class="btn-secondary" @click="cancelForm">Отмена</button>
        </div>
      </div>

      <div v-if="!masters.length" class="empty">Мастеров нет</div>
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Имя</th>
              <th>Фамилия</th>
              <th>Телефон</th>
              <th>Регион</th>
              <th>Адрес</th>
              <th>Рейтинг</th>
              <th>Отзывы</th>
              <th>Услуги</th>
              <th>Верифицирован</th>
              <th>Дата</th>
              <th></th>
            </tr>
          </thead>
          <template v-for="m in masters" :key="m.id">
            <tr
              class="master-row"
              :class="{ 'master-row--expanded': expandedMasterId === m.id }"
              @click="toggleMaster(m.id)"
            >
              <td>{{ m.id }}</td>
              <td>{{ m.firstName }}</td>
              <td>{{ m.lastName }}</td>
              <td>{{ m.phone || '—' }}</td>
              <td>{{ m.regionName }}</td>
              <td>{{ m.address || '—' }}</td>
              <td>
                <span class="rating">★ {{ Number(m.rating).toFixed(1) }}</span>
              </td>
              <td>{{ m.reviewsCount }}</td>
              <td>
                <span class="services-count" v-if="m.services?.length">
                  {{ m.services.length }} шт.
                </span>
                <span v-else class="no-services">нет</span>
              </td>
              <td>
                <span v-if="m.isVerified" class="verified">Да</span>
                <span v-else class="not-verified">Нет</span>
              </td>
              <td>{{ m.createdAt ? formatDate(m.createdAt) : '—' }}</td>
              <td>
                <div class="row-actions">
                  <button class="btn-icon" @click.stop="startEdit(m)" title="Редактировать">
                    Редактировать
                  </button>
                  <button
                    class="btn-icon btn-icon--danger"
                    @click.stop="deleteMaster(m.id)"
                    title="Удалить"
                  >
                    Удалить
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="expandedMasterId === m.id" class="master-detail">
              <td colspan="12">
                <div class="master-detail__content">
                  <div class="detail-section" v-if="m.bio">
                    <h4>О себе</h4>
                    <p>{{ m.bio }}</p>
                  </div>
                  <div class="detail-section" v-if="m.services?.length">
                    <h4>Услуги ({{ m.services.length }})</h4>
                    <div class="services-grid">
                      <div v-for="s in m.services" :key="s.id" class="service-chip">
                        <span class="service-chip__name">{{ s.name }}</span>
                        <span class="service-chip__price">{{ Number(s.price).toLocaleString('ru-RU') }} ₽</span>
                        <span class="service-chip__duration">{{ s.durationMinutes }} мин</span>
                        <span v-if="s.categoryName" class="service-chip__cat">{{ s.categoryName }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="detail-section" v-else>
                    <p class="no-data">Услуг пока нет</p>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </table>
      </div>
    </template>
  </div>
</template>

<style scoped>
.admin-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-header__left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.page-header__left h1 {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin: 0;
}

.nav-link {
  color: #e63946;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
}

.nav-link:hover {
  text-decoration: underline;
}

.page-header__right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  color: #666;
  font-size: 0.85rem;
}

.btn-logout {
  padding: 8px 20px;
  background: #666;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
}

.btn-logout:hover {
  background: #444;
}

.tabs {
  display: flex;
  gap: 0;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #eee;
}

.tab {
  padding: 12px 24px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #888;
  cursor: pointer;
}

.tab--active {
  color: #e63946;
  border-bottom-color: #e63946;
}

.tab:hover {
  color: #333;
}

.error-msg {
  color: #e63946;
  text-align: center;
  padding: 1rem;
  margin-bottom: 1rem;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.empty {
  text-align: center;
  color: #999;
  padding: 3rem;
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.data-table th {
  text-align: left;
  padding: 12px 16px;
  background: #f8f9fa;
  font-size: 0.8rem;
  font-weight: 700;
  color: #555;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.data-table td {
  padding: 12px 16px;
  border-top: 1px solid #f0f0f0;
  font-size: 0.9rem;
  color: #333;
}

.data-table tr:hover td {
  background: #fafafa;
}

.role-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-right: 4px;
}

.role-badge--admin {
  background: #fce4ec;
  color: #c62828;
}

.role-badge--master {
  background: #e3f2fd;
  color: #1565c0;
}

.role-badge--user {
  background: #e8f5e9;
  color: #2e7d32;
}

.rating {
  font-weight: 700;
  color: #f59e0b;
}

.verified {
  color: #28a745;
  font-weight: 600;
}

.not-verified {
  color: #999;
}

.btn-icon {
  padding: 6px 14px;
  background: none;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
}

.btn-icon--danger {
  color: #c62828;
  border-color: #fdd;
}

.btn-icon--danger:hover {
  background: #fee;
  border-color: #c62828;
}

.tab-actions {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 1rem;
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

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.form-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
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

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.master-row {
  cursor: pointer;
}

.master-row--expanded td {
  background: #f8f9fa;
}

.master-detail td {
  padding: 0 !important;
  background: #f8f9fa;
}

.master-detail__content {
  padding: 1.5rem;
  border-top: 2px solid #e0e0e0;
}

.detail-section {
  margin-bottom: 1.25rem;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-section h4 {
  margin: 0 0 0.75rem;
  font-size: 0.9rem;
  color: #555;
  font-weight: 700;
}

.detail-section p {
  margin: 0;
  color: #333;
  line-height: 1.5;
}

.services-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.service-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 6px 12px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.85rem;
}

.service-chip__name {
  font-weight: 600;
  color: #333;
}

.service-chip__price {
  color: #e63946;
  font-weight: 700;
}

.service-chip__duration {
  color: #888;
}

.service-chip__cat {
  padding: 2px 6px;
  background: #f0f0f0;
  border-radius: 4px;
  font-size: 0.75rem;
  color: #666;
}

.no-data {
  color: #999;
  font-style: italic;
}

.row-actions {
  display: flex;
  gap: 0.25rem;
}
</style>
