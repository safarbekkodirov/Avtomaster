<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { mastersApi } from '@/api/masters'
import type { ServiceCategory } from '@/types/master.types'

const router    = useRouter()
const authStore = useAuthStore()

const categories = ref<ServiceCategory[]>([])
const loading    = ref(true)
const error      = ref('')
const showForm   = ref(false)
const editingId  = ref<number | null>(null)

const form = ref({
  name:        '',
  description: '',
  icon:        '',
})

const icons = [
  { value: 'wrench',   label: '🔧 Ремонт' },
  { value: 'car',      label: '🚗 Кузов' },
  { value: 'zap',      label: '⚡ Электрика' },
  { value: 'circle',   label: '⭕ Дөңгөлөк' },
  { value: 'droplets', label: '💧 Жууу' },
  { value: 'settings', label: '⚙️ Жабдыктар' },
  { value: 'shield',   label: '🛡️ Коопсуздук' },
  { value: 'tool',     label: '🔨 Түзөтүү' },
]

async function loadCategories() {
  loading.value = true
  try {
    const result = await mastersApi.getCategories()
    categories.value = result.member
  } catch {
    error.value = 'Ошибка загрузки'
  } finally {
    loading.value = false
  }
}

function startCreate() {
  editingId.value = null
  form.value = { name: '', description: '', icon: '' }
  showForm.value = true
}

function startEdit(cat: ServiceCategory) {
  editingId.value = cat.id
  form.value = {
    name:        cat.name,
    description: cat.description ?? '',
    icon:        cat.icon ?? '',
  }
  showForm.value = true
}

function cancelForm() {
  showForm.value = false
  editingId.value = null
}

async function saveCategory() {
  error.value = ''
  try {
    if (editingId.value) {
      await mastersApi.updateCategory(editingId.value, {
        name:        form.value.name,
        description: form.value.description || undefined,
        icon:        form.value.icon || undefined,
      })
    } else {
      await mastersApi.createCategory({
        name:        form.value.name,
        description: form.value.description || undefined,
        icon:        form.value.icon || undefined,
      })
    }
    showForm.value = false
    editingId.value = null
    await loadCategories()
  } catch {
    error.value = 'Ошибка сохранения'
  }
}

async function deleteCategory(id: number) {
  if (!confirm('Удалить категорию?')) return
  try {
    await mastersApi.deleteCategory(id)
    await loadCategories()
  } catch {
    error.value = 'Ошибка удаления'
  }
}

function logout() {
  authStore.logout()
  router.push({ name: 'login' })
}

onMounted(loadCategories)
</script>

<template>
  <div class="categories-page">
    <header class="page-header">
      <h1>Категорияларды башкаруу</h1>
      <div class="page-actions">
        <button class="btn-primary" @click="startCreate">+ Жаңы категория</button>
        <button class="btn-logout" @click="logout">Чыгуу</button>
      </div>
    </header>

    <div v-if="error" class="error-msg">{{ error }}</div>

    <!-- Форма -->
    <div v-if="showForm" class="form-card">
      <h2>{{ editingId ? 'Түзөтүү' : 'Жаңы категория' }}</h2>
      <div class="form-grid">
        <div class="form-group">
          <label>Аты *</label>
          <input v-model="form.name" placeholder="Категория аты" />
        </div>
        <div class="form-group">
          <label>Иконка</label>
          <select v-model="form.icon">
            <option value="">Тандоо...</option>
            <option v-for="ic in icons" :key="ic.value" :value="ic.value">{{ ic.label }}</option>
          </select>
        </div>
        <div class="form-group full-width">
          <label>Түшүндүрмө</label>
          <textarea v-model="form.description" rows="2" placeholder="Категория жөнүндө..." />
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-primary" @click="saveCategory">Сактоо</button>
        <button class="btn-secondary" @click="cancelForm">Отмена</button>
      </div>
    </div>

    <!-- Тизме -->
    <div v-if="loading" class="loading">Загрузка...</div>

    <div v-else-if="categories.length" class="categories-list">
      <div v-for="cat in categories" :key="cat.id" class="category-item">
        <div class="category-item__info">
          <span class="category-item__icon">{{ icons.find(i => i.value === cat.icon)?.label?.split(' ')[0] || '📁' }}</span>
          <div class="category-item__text">
            <span class="category-item__name">{{ cat.name }}</span>
            <span class="category-item__slug">{{ cat.slug }}</span>
            <span v-if="cat.description" class="category-item__desc">{{ cat.description }}</span>
          </div>
        </div>
        <div class="category-item__actions">
          <button class="btn-icon" @click="startEdit(cat)">✏️</button>
          <button class="btn-icon btn-icon--danger" @click="deleteCategory(cat.id)">🗑️</button>
        </div>
      </div>
    </div>

    <p v-else class="empty">Категориялар жок</p>
  </div>
</template>

<style scoped>
.categories-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin: 0;
}

.page-actions {
  display: flex;
  gap: 0.75rem;
}

.error-msg {
  color: #e63946;
  text-align: center;
  padding: 1rem;
  margin-bottom: 1rem;
}

.form-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
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

.btn-logout {
  padding: 10px 24px;
  background: #666;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.95rem;
  cursor: pointer;
}

.btn-logout:hover {
  background: #444;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.categories-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background: white;
  border: 1px solid #eee;
  border-radius: 10px;
  transition: box-shadow 0.2s;
}

.category-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.category-item__info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-item__icon {
  font-size: 1.5rem;
  width: 40px;
  text-align: center;
}

.category-item__text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.category-item__name {
  font-weight: 600;
  font-size: 1rem;
}

.category-item__slug {
  color: #999;
  font-size: 0.8rem;
  font-family: monospace;
}

.category-item__desc {
  color: #666;
  font-size: 0.85rem;
}

.category-item__actions {
  display: flex;
  gap: 0.25rem;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 6px;
  border-radius: 6px;
}

.btn-icon:hover {
  background: #f0f0f0;
}

.btn-icon--danger:hover {
  background: #fee;
}

.empty {
  text-align: center;
  color: #999;
  padding: 3rem;
}
</style>
