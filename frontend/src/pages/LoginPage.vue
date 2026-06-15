<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const router = useRouter()
const store  = useAuthStore()
const email  = ref('')
const pass   = ref('')
const error  = ref('')

async function submit() {
  try {
    await store.login({ email: email.value, password: pass.value })
    router.push({ name: 'dashboard' })
  } catch {
    error.value = 'Неверный email или пароль'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-form">
      <h2>Вход</h2>
      <input v-model="email" type="email" placeholder="Email" />
      <input v-model="pass"  type="password" placeholder="Пароль" />
      <p v-if="error" class="error">{{ error }}</p>
      <button @click="submit">Войти</button>
      <RouterLink to="/register">Нет аккаунта? Зарегистрироваться</RouterLink>
    </div>
  </div>
</template>

<style scoped>
.auth-page { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f5f5f5; }
.auth-form { background:white; padding:2rem; border-radius:8px; width:100%; max-width:400px; display:flex; flex-direction:column; gap:1rem; box-shadow:0 2px 8px rgba(0,0,0,.1); }
input { padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1rem; }
button { padding:12px; background:#e63946; color:white; border:none; border-radius:6px; font-size:1rem; cursor:pointer; }
.error { color:#e63946; font-size:.9rem; }
</style>
