<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { mastersApi } from '@/api/masters'
import type { Master, ServiceCategory } from '@/types/master.types'

const router = useRouter()
const auth   = useAuthStore()

const categories = ref<ServiceCategory[]>([])
const topMasters = ref<Master[]>([])
const loading    = ref(true)

const categoryIcons: Record<string, string> = {
  wrench: 'W',
  car: 'C',
  zap: 'E',
  circle: 'D',
  droplets: 'W',
  settings: 'S',
  shield: 'K',
  tool: 'R',
}

async function loadData() {
  loading.value = true
  try {
    const [catRes, masterRes] = await Promise.all([
      mastersApi.getCategories(),
      mastersApi.search({ sortBy: 'rating', perPage: 6 } as any),
    ])
    categories.value = catRes.member
    topMasters.value = masterRes.data
  } catch { /* ignore */ } finally {
    loading.value = false
  }
}

function searchByCategory(slug: string) {
  router.push({ name: 'search', query: { category: slug } })
}

function goToSearch() {
  router.push({ name: 'search' })
}

onMounted(loadData)
</script>

<template>
  <div class="home">
    <!-- Hero -->
    <section class="hero">
      <div class="hero__bg" />
      <div class="hero__content">
        <span class="hero__badge">Avtomaster.kg</span>
        <h1 class="hero__title">
          Автомастерди<br />
          <span class="hero__title--accent">оңой табыңыз</span>
        </h1>
        <p class="hero__subtitle">
          Кыргызстандагы мыкты автомастерлерди издөө, баалоо жана онлайн жазылуу
        </p>
        <div class="hero__search">
          <input
            type="text"
            placeholder="Эмне керек? Мисалы: масло алмаштыруу..."
            class="hero__input"
            @focus="goToSearch"
            readonly
          />
          <button class="hero__btn" @click="goToSearch">🔍 Изде</button>
        </div>
        <div class="hero__stats">
          <div class="hero__stat">
            <span class="hero__stat-num">{{ topMasters.length }}+</span>
            <span class="hero__stat-label">Мастер</span>
          </div>
          <div class="hero__stat">
            <span class="hero__stat-num">{{ categories.length }}</span>
            <span class="hero__stat-label">Категория</span>
          </div>
          <div class="hero__stat">
            <span class="hero__stat-num">7</span>
            <span class="hero__stat-label">Шаар</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Категориялар -->
    <section class="section">
      <div class="container">
        <div class="section__header">
          <h2 class="section__title">Кызмат түрлөрү</h2>
          <p class="section__desc">Керектүү кызматты тандаңыз</p>
        </div>
        <div class="categories" v-if="categories.length">
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="category-card"
            @click="searchByCategory(cat.slug)"
          >
            <span class="category-card__icon">{{ categoryIcons[cat.icon] || '?' }}</span>
            <span class="category-card__name">{{ cat.name }}</span>
            <span v-if="cat.description" class="category-card__desc">{{ cat.description }}</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Топ мастерлер -->
    <section class="section section--gray">
      <div class="container">
        <div class="section__header">
          <h2 class="section__title">Топ мастерлер</h2>
          <button class="section__link" @click="goToSearch">Бардыгы →</button>
        </div>
        <div class="masters-grid" v-if="topMasters.length">
          <div
            v-for="master in topMasters"
            :key="master.id"
            class="master-card"
            @click="router.push({ name: 'master-profile', params: { id: master.id } })"
          >
            <div class="master-card__top">
              <div class="master-card__avatar">
                {{ master.firstName[0] }}{{ master.lastName[0] }}
              </div>
              <div class="master-card__rating">
                <span class="master-card__stars">★</span>
                {{ Number(master.rating).toFixed(1) }}
                <span class="master-card__count">({{ master.reviewsCount }})</span>
              </div>
            </div>
            <h3 class="master-card__name">{{ master.firstName }} {{ master.lastName }}</h3>
            <p class="master-card__region">📍 {{ master.regionName || 'Не указан' }}</p>
            <div v-if="master.services.length" class="master-card__services">
              <span
                v-for="service in master.services.slice(0, 2)"
                :key="service.id"
                class="master-card__service"
              >
                {{ service.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Преимущества -->
    <section class="section">
      <div class="container">
        <div class="section__header">
          <h2 class="section__title">Эмне үчүн Avtomaster?</h2>
        </div>
        <div class="features">
          <div class="feature">
            <div class="feature__icon">01</div>
            <h3 class="feature__title">Оңой издөө</h3>
            <p class="feature__desc">Регион, категория, рейтинг боюнча тез табыңыз</p>
          </div>
          <div class="feature">
            <div class="feature__icon">02</div>
            <h3 class="feature__title">Онлайн жазылуу</h3>
            <p class="feature__desc">Ыңгайлуу убакытты тандаңыз, бир нече секундада</p>
          </div>
          <div class="feature">
            <div class="feature__icon">03</div>
            <h3 class="feature__title">Чыныгы баа</h3>
            <p class="feature__desc">Башка кардарлардын пикирлери боюнча тандаңыз</p>
          </div>
          <div class="feature">
            <div class="feature__icon">04</div>
            <h3 class="feature__title">Жакынкылар</h3>
            <p class="feature__desc">Геолокация менен жаныңыздагыларды табыңыз</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta">
      <div class="container cta__inner">
        <h2 class="cta__title">Мастер болгуңуз келеби?</h2>
        <p class="cta__desc">Профиль түзүңүз жана кардарларды табыңыз</p>
        <button
          v-if="!auth.isAuth"
          class="cta__btn"
          @click="router.push({ name: 'register' })"
        >
          Тиркеме кошулуу
        </button>
        <button
          v-else
          class="cta__btn"
          @click="router.push({ name: 'master-dashboard' })"
        >
          Кабинетке өтүү
        </button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.home { min-height: 100vh; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

/* Hero */
.hero {
  position: relative;
  display: flex; align-items: center; justify-content: center;
  min-height: 92vh; overflow: hidden;
}
.hero__bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
}
.hero__bg::after {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(circle at 30% 50%, rgba(230,57,70,0.15), transparent 60%),
              radial-gradient(circle at 70% 80%, rgba(58,134,255,0.1), transparent 50%);
}
.hero__content {
  position: relative; z-index: 1;
  text-align: center; color: white;
  max-width: 700px; padding: 2rem;
}
.hero__badge {
  display: inline-block; padding: 6px 18px; border-radius: 50px;
  background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.15);
  font-size: 0.85rem; font-weight: 600; letter-spacing: 0.5px;
  margin-bottom: 1.5rem;
}
.hero__title {
  font-size: 3.5rem; font-weight: 800; line-height: 1.1; margin: 0 0 1rem;
}
.hero__title--accent {
  background: linear-gradient(135deg, #e63946, #ff6b6b);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero__subtitle {
  font-size: 1.15rem; opacity: 0.8; margin: 0 0 2rem; line-height: 1.6;
}
.hero__search {
  display: flex; gap: 0; max-width: 520px; margin: 0 auto 2.5rem;
  background: rgba(255,255,255,0.12); backdrop-filter: blur(10px);
  border-radius: 14px; border: 1px solid rgba(255,255,255,0.15);
  padding: 6px;
}
.hero__input {
  flex: 1; padding: 14px 18px; border: none; border-radius: 10px;
  background: transparent; color: white; font-size: 1rem; outline: none;
}
.hero__input::placeholder { color: rgba(255,255,255,0.5); }
.hero__btn {
  padding: 14px 28px; background: #e63946; color: white; border: none;
  border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer;
  transition: background 0.2s;
}
.hero__btn:hover { background: #d32f3f; }

.hero__stats {
  display: flex; gap: 3rem; justify-content: center;
}
.hero__stat { text-align: center; }
.hero__stat-num {
  display: block; font-size: 1.8rem; font-weight: 800;
}
.hero__stat-label { font-size: 0.85rem; opacity: 0.7; }

/* Sections */
.section { padding: 5rem 0; }
.section--gray { background: #f8f9fa; }
.section__header {
  display: flex; justify-content: space-between; align-items: end;
  margin-bottom: 2.5rem; flex-wrap: wrap; gap: 0.5rem;
}
.section__title { font-size: 1.8rem; font-weight: 800; color: #1a1a2e; margin: 0; }
.section__desc { color: #888; margin: 0.25rem 0 0; font-size: 1rem; }
.section__link {
  background: none; border: none; color: #e63946; font-size: 0.95rem;
  font-weight: 600; cursor: pointer;
}
.section__link:hover { text-decoration: underline; }

/* Категориялар */
.categories {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}
.category-card {
  display: flex; flex-direction: column; align-items: center;
  padding: 1.5rem 1rem; background: white; border: 1px solid #eee;
  border-radius: 16px; cursor: pointer;
  transition: all 0.25s;
  text-align: center;
}
.category-card:hover {
  border-color: #e63946; transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(230,57,70,0.12);
}
.category-card__icon {
  width: 48px; height: 48px; border-radius: 12px;
  background: linear-gradient(135deg, #1a1a2e, #e63946);
  color: white; font-weight: 800; font-size: 1.1rem;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 0.75rem;
}
.category-card__name { font-weight: 700; font-size: 0.95rem; color: #1a1a2e; }
.category-card__desc {
  font-size: 0.75rem; color: #999; margin-top: 4px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

/* Топ мастерлер */
.masters-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.25rem;
}
.master-card {
  background: white; border: 1px solid #eee; border-radius: 16px;
  padding: 1.5rem; cursor: pointer;
  transition: all 0.25s;
}
.master-card:hover {
  border-color: #e63946; transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.master-card__top {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 0.75rem;
}
.master-card__avatar {
  width: 48px; height: 48px; border-radius: 12px;
  background: linear-gradient(135deg, #1a1a2e, #e63946);
  color: white; font-weight: 700; font-size: 0.9rem;
  display: flex; align-items: center; justify-content: center;
}
.master-card__rating { font-weight: 700; font-size: 0.95rem; color: #f59e0b; }
.master-card__stars { margin-right: 2px; }
.master-card__count { color: #aaa; font-weight: 400; font-size: 0.8rem; }
.master-card__name { margin: 0 0 0.25rem; font-size: 1.05rem; color: #1a1a2e; }
.master-card__region { margin: 0 0 0.5rem; font-size: 0.85rem; color: #888; }
.master-card__services { display: flex; flex-wrap: wrap; gap: 6px; }
.master-card__service {
  padding: 3px 10px; background: #f5f5f5; border-radius: 6px;
  font-size: 0.75rem; color: #555;
}

/* Преимущества */
.features {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}
.feature {
  padding: 2rem; background: white; border-radius: 16px;
  border: 1px solid #eee; text-align: center;
  transition: transform 0.25s;
}
.feature:hover { transform: translateY(-3px); }
.feature__icon {
  font-size: 1rem; font-weight: 800; color: #e63946; margin-bottom: 1rem;
  width: 40px; height: 40px; border-radius: 10px;
  background: #fef2f2; display: flex; align-items: center; justify-content: center;
}
.feature__title { margin: 0 0 0.5rem; font-size: 1.1rem; color: #1a1a2e; }
.feature__desc { margin: 0; color: #888; font-size: 0.9rem; line-height: 1.5; }

/* CTA */
.cta {
  padding: 4rem 0;
  background: linear-gradient(135deg, #1a1a2e, #302b63);
}
.cta__inner { text-align: center; color: white; }
.cta__title { font-size: 1.8rem; font-weight: 800; margin: 0 0 0.5rem; }
.cta__desc { margin: 0 0 1.5rem; opacity: 0.8; }
.cta__btn {
  padding: 14px 36px; background: #e63946; color: white; border: none;
  border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer;
  transition: background 0.2s;
}
.cta__btn:hover { background: #d32f3f; }

@media (max-width: 640px) {
  .hero__title { font-size: 2.2rem; }
  .hero__stats { gap: 1.5rem; }
  .hero__stat-num { font-size: 1.3rem; }
  .categories { grid-template-columns: repeat(2, 1fr); }
}
</style>
