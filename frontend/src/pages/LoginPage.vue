<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const router = useRouter()
const store  = useAuthStore()
const email  = ref('')
const pass   = ref('')
const error  = ref('')
const showPass = ref(false)

async function submit() {
  error.value = ''
  try {
    await store.login({ email: email.value, password: pass.value })
    if (store.isMaster) {
      router.push({ name: 'master-dashboard' })
    } else {
      router.push({ name: 'search' })
    }
  } catch {
    error.value = 'Неверный email или пароль'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-card__logo">A</div>
      <h1 class="auth-card__title">Добро пожаловать</h1>
      <p class="auth-card__subtitle">Войдите в свой аккаунт</p>

      <form class="auth-form" @submit.prevent="submit">
        <div class="field">
          <label class="field__label">Email</label>
          <input v-model="email" type="email" class="field__input" placeholder="mail@example.com" />
        </div>
        <div class="field">
          <label class="field__label">Пароль</label>
          <div class="field__password">
            <input v-model="pass" :type="showPass ? 'text' : 'password'" class="field__input" placeholder="••••••••" />
            <button type="button" class="field__toggle" @click="showPass = !showPass">
              {{ showPass ? 'Скрыть' : 'Показать' }}
            </button>
          </div>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="btn-primary">Войти</button>
      </form>

      <p class="auth-card__footer">
        Нет аккаунта?
        <RouterLink to="/register" class="auth-card__link">Зарегистрироваться</RouterLink>
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
  background: white; border-radius: 20px; padding: 2.5rem; width: 100%; max-width: 420px;
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
.auth-card__subtitle { margin: 0 0 2rem; color: #888; font-size: 0.95rem; }
.auth-form { display: flex; flex-direction: column; gap: 1.25rem; }
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
