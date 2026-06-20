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
const isLoading = ref(false)

async function submit() {
  error.value = ''
  isLoading.value = true
  try {
    await store.login({ email: email.value, password: pass.value })
    if (store.isAdmin) {
      router.push({ name: 'admin' })
    } else if (store.isMaster) {
      router.push({ name: 'master-dashboard' })
    } else {
      router.push({ name: 'search' })
    }
  } catch {
    error.value = 'Неверный email или пароль'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-bg">
      <div class="auth-bg__orb auth-bg__orb--1" />
      <div class="auth-bg__orb auth-bg__orb--2" />
    </div>

    <div class="auth-card">
      <RouterLink to="/" class="auth-card__back">← На главную</RouterLink>

      <div class="auth-card__logo">A</div>
      <h1 class="auth-card__title">Добро пожаловать</h1>
      <p class="auth-card__subtitle">Войдите в свой аккаунт Avtomaster</p>

      <form class="auth-form" @submit.prevent="submit">
        <div class="field">
          <label class="field__label">Email</label>
          <input v-model="email" type="email" class="field__input" placeholder="your@email.com" required />
        </div>
        <div class="field">
          <label class="field__label">Пароль</label>
          <div class="field__password">
            <input v-model="pass" :type="showPass ? 'text' : 'password'" class="field__input" placeholder="Введите пароль" required />
            <button type="button" class="field__toggle" @click="showPass = !showPass">
              {{ showPass ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="btn-primary" :disabled="isLoading">
          <span v-if="isLoading" class="btn-spinner" />
          {{ isLoading ? 'Вход...' : 'Войти' }}
        </button>
      </form>

      <div class="auth-card__divider">
        <span>или</span>
      </div>

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
  background: #0a0a1a; padding: 2rem; position: relative; overflow: hidden;
}
.auth-bg { position: absolute; inset: 0; }
.auth-bg__orb {
  position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.3;
  animation: floatOrb 10s ease-in-out infinite;
}
.auth-bg__orb--1 {
  width: 400px; height: 400px; top: -15%; right: -10%;
  background: radial-gradient(circle, #e63946, transparent 70%);
}
.auth-bg__orb--2 {
  width: 300px; height: 300px; bottom: -10%; left: -5%;
  background: radial-gradient(circle, #3a86ff, transparent 70%);
  animation-delay: -5s;
}
@keyframes floatOrb {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(30px, -20px) scale(1.1); }
}

.auth-card {
  position: relative; z-index: 1;
  background: rgba(255,255,255,0.05); backdrop-filter: blur(40px);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 24px; padding: 2.5rem;
  width: 100%; max-width: 420px;
}

.auth-card__back {
  display: inline-block; margin-bottom: 1.5rem;
  color: rgba(255,255,255,0.4); text-decoration: none;
  font-size: 0.85rem; font-weight: 500; transition: color 0.2s;
}
.auth-card__back:hover { color: rgba(255,255,255,0.7); }

.auth-card__logo {
  width: 52px; height: 52px; border-radius: 14px;
  background: linear-gradient(135deg, #e63946, #ff6b6b);
  color: white; font-weight: 900; font-size: 1.4rem;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.5rem;
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}
.auth-card__title {
  margin: 0 0 0.25rem; font-size: 1.6rem; font-weight: 800; color: white;
}
.auth-card__subtitle {
  margin: 0 0 2rem; color: rgba(255,255,255,0.4); font-size: 0.95rem;
}

.auth-form { display: flex; flex-direction: column; gap: 1.25rem; }

.field__label {
  display: block; font-size: 0.8rem; font-weight: 600;
  color: rgba(255,255,255,0.5); margin-bottom: 6px;
  text-transform: uppercase; letter-spacing: 0.5px;
}
.field__input {
  width: 100%; padding: 14px 16px;
  background: rgba(255,255,255,0.06); border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 12px; font-size: 0.95rem; color: white;
  outline: none; transition: all 0.2s; box-sizing: border-box;
}
.field__input::placeholder { color: rgba(255,255,255,0.25); }
.field__input:focus {
  border-color: rgba(230,57,70,0.5);
  background: rgba(255,255,255,0.08);
  box-shadow: 0 0 0 3px rgba(230,57,70,0.1);
}

.field__password { position: relative; }
.field__toggle {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; font-size: 1rem; cursor: pointer;
  padding: 4px; opacity: 0.5; transition: opacity 0.2s;
}
.field__toggle:hover { opacity: 1; }

.error {
  margin: 0; color: #ff6b6b; font-size: 0.9rem;
  background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.2);
  padding: 10px 14px; border-radius: 10px;
}

.btn-primary {
  width: 100%; padding: 14px; border: none; border-radius: 12px;
  background: linear-gradient(135deg, #e63946, #d32f3f);
  color: white; font-size: 1rem; font-weight: 700; cursor: pointer;
  transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(230,57,70,0.3);
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-spinner {
  width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white; border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.auth-card__divider {
  display: flex; align-items: center; gap: 1rem;
  margin: 1.5rem 0; color: rgba(255,255,255,0.2); font-size: 0.85rem;
}
.auth-card__divider::before, .auth-card__divider::after {
  content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08);
}

.auth-card__footer {
  text-align: center; margin: 0; font-size: 0.9rem;
  color: rgba(255,255,255,0.4);
}
.auth-card__link {
  color: #e63946; font-weight: 600; text-decoration: none;
  transition: color 0.2s;
}
.auth-card__link:hover { color: #ff6b6b; }
</style>
