<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { mastersApi } from '@/api/masters'
import type { Region } from '@/types/master.types'

const route   = useRoute()
const router  = useRouter()
const store   = useAuthStore()
const firstName = ref('')
const lastName  = ref('')
const email     = ref('')
const pass      = ref('')
const role      = ref<'client'|'master'>((route.query.role as 'client'|'master') || 'client')
const phone     = ref('')
const regionName = ref('')
const address   = ref('')
const regions   = ref<Region[]>([])
const error     = ref('')
const showPass  = ref(false)

onMounted(async () => {
  try {
    const result = await mastersApi.getRegions()
    regions.value = result.member
  } catch { /* ignore */ }
})

async function submit() {
  error.value = ''
  try {
    await store.register({
      firstName: firstName.value,
      lastName: lastName.value,
      email: email.value,
      password: pass.value,
      role: role.value,
      phone: phone.value || undefined,
      regionName: regionName.value || undefined,
      address: address.value || undefined,
    } as any)
    if (role.value === 'master') {
      router.push({ name: 'master-dashboard' })
    } else {
      router.push({ name: 'search' })
    }
  } catch {
    error.value = 'Ошибка регистрации'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-card__logo">A</div>
      <h1 class="auth-card__title">Создать аккаунт</h1>
      <p class="auth-card__subtitle">Заполните форму для регистрации</p>

      <form class="auth-form" @submit.prevent="submit">
        <div class="form-row">
          <div class="field">
            <label class="field__label">Имя</label>
            <input v-model="firstName" class="field__input" placeholder="Имя" />
          </div>
          <div class="field">
            <label class="field__label">Фамилия</label>
            <input v-model="lastName" class="field__input" placeholder="Фамилия" />
          </div>
        </div>
        <div class="field">
          <label class="field__label">Email</label>
          <input v-model="email" type="email" class="field__input" placeholder="mail@example.com" />
        </div>
        <div class="field">
          <label class="field__label">Пароль</label>
          <div class="field__password">
            <input v-model="pass" :type="showPass ? 'text' : 'password'" class="field__input" placeholder="Минимум 6 символов" />
            <button type="button" class="field__toggle" @click="showPass = !showPass">
              {{ showPass ? 'Скрыть' : 'Показать' }}
            </button>
          </div>
        </div>
        <div class="field">
          <label class="field__label">Я кто?</label>
          <div class="role-select">
            <button type="button" class="role-option" :class="{ 'role-option--active': role === 'client' }" @click="role = 'client'">
              <span class="role-option__title">Клиент</span>
              <span class="role-option__desc">Ищу мастера</span>
            </button>
            <button type="button" class="role-option" :class="{ 'role-option--active': role === 'master' }" @click="role = 'master'">
              <span class="role-option__title">Мастер</span>
              <span class="role-option__desc">Предлагаю услуги</span>
            </button>
          </div>
        </div>

        <div class="field" v-if="role === 'master'">
          <label class="field__label">Телефон</label>
          <input v-model="phone" class="field__input" placeholder="+996..." />
        </div>
        <div class="field" v-if="role === 'master'">
          <label class="field__label">Регион</label>
          <select v-model="regionName" class="field__input">
            <option value="">Выберите регион</option>
            <option v-for="r in regions" :key="r.id" :value="r.name">{{ r.name }}</option>
          </select>
        </div>
        <div class="field" v-if="role === 'master'">
          <label class="field__label">Адрес</label>
          <input v-model="address" class="field__input" placeholder="ул. Ленина 10..." />
        </div>
        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="btn-primary">Зарегистрироваться</button>
      </form>

      <p class="auth-card__footer">
        Уже есть аккаунт?
        <RouterLink to="/login" class="auth-card__link">Войти</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: #f0f2f5; padding: 2rem;
}
.auth-card {
  background: white; border-radius: 20px; padding: 2.5rem; width: 100%; max-width: 480px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
}
.auth-card__logo {
  width: 48px; height: 48px; border-radius: 14px;
  background: linear-gradient(135deg, #1a1a2e, #e63946);
  color: white; font-weight: 800; font-size: 1.3rem;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.5rem;
}
.auth-card__title { margin: 0 0 0.25rem; font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
.auth-card__subtitle { margin: 0 0 1.75rem; color: #888; font-size: 0.95rem; }
.auth-form { display: flex; flex-direction: column; gap: 1.1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.field__label { display: block; font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 6px; }
.field__input {
  width: 100%; padding: 12px 14px; border: 1.5px solid #e0e0e0; border-radius: 10px;
  font-size: 0.95rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;
}
.field__input:focus { border-color: #e63946; }
.field__password { position: relative; }
.field__toggle {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: #888; font-size: 0.8rem; cursor: pointer;
}
.role-select { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.role-option {
  padding: 14px; border: 2px solid #e0e0e0; border-radius: 12px; background: white;
  cursor: pointer; text-align: left; transition: all 0.2s;
}
.role-option:hover { border-color: #ccc; }
.role-option--active { border-color: #e63946; background: #fef2f2; }
.role-option__title { display: block; font-weight: 700; font-size: 0.95rem; color: #1a1a2e; }
.role-option__desc { display: block; font-size: 0.8rem; color: #888; margin-top: 2px; }
.error { margin: 0; color: #e63946; font-size: 0.9rem; background: #fef2f2; padding: 10px 14px; border-radius: 8px; }
.btn-primary {
  width: 100%; padding: 13px; background: #e63946; color: white; border: none;
  border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer;
  transition: background 0.2s;
}
.btn-primary:hover { background: #d32f3f; }
.auth-card__footer { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #888; }
.auth-card__link { color: #e63946; font-weight: 600; text-decoration: none; }
.auth-card__link:hover { text-decoration: underline; }
</style>
