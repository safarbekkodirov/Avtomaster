<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
  <div class="home">
    <header class="home__hero">
      <h1>🔧 Avtomaster</h1>
      <p>Найдите автомастера рядом с вами</p>
      <button @click="router.push({ name: 'search' })">Найти мастера</button>
    </header>
  </div>
</template>

<style scoped>
.home__hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  gap: 1rem;
  text-align: center;
  background: linear-gradient(135deg, #1a1a2e, #e63946);
  color: white;
}
.home__hero h1 { font-size: 3rem; margin: 0; }
.home__hero p  { font-size: 1.2rem; opacity: 0.9; }
.home__hero button {
  padding: 12px 32px;
  background: white;
  color: #e63946;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
}
</style>
