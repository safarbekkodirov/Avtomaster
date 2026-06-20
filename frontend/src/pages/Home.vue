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

const categoryEmoji: Record<string, string> = {
  wrench:   '🔧',
  car:      '🚗',
  zap:      '⚡',
  circle:   '🛞',
  droplets: '💧',
  settings: '⚙️',
  shield:   '🛡️',
  tool:     '🔨',
}

async function loadData() {
  loading.value = true
  try {
    const [catRes, masterRes] = await Promise.all([
      mastersApi.getCategories(),
      mastersApi.search({ perPage: 6 } as any),
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
      <div class="hero__bg">
        <div class="hero__orb hero__orb--1" />
        <div class="hero__orb hero__orb--2" />
        <div class="hero__orb hero__orb--3" />
      </div>

      <div class="hero__content">
        <span class="hero__badge">
          <span class="hero__badge-dot" />
          Avtomaster.kg
        </span>

        <h1 class="hero__title">
          Найдите лучшего<br />
          <span class="hero__title--accent">автомастера</span>
        </h1>

        <p class="hero__subtitle">
          Быстрый поиск, честные отзывы и онлайн-запись<br />
          к проверенным автомастерам Кыргызстана
        </p>

        <div class="hero__search">
          <span class="hero__search-icon">🔍</span>
          <input
            type="text"
            placeholder="Замена масла, ремонт тормозов, диагностика..."
            class="hero__input"
            @focus="goToSearch"
            readonly
          />
          <button class="hero__search-btn" @click="goToSearch">
            Найти мастера
          </button>
        </div>

        <div class="hero__tags">
          <button class="hero__tag" @click="goToSearch">Масло</button>
          <button class="hero__tag" @click="goToSearch">Тормоза</button>
          <button class="hero__tag" @click="goToSearch">Двигатель</button>
          <button class="hero__tag" @click="goToSearch">Кузов</button>
          <button class="hero__tag" @click="goToSearch">Диагностика</button>
        </div>

        <div class="hero__stats">
          <div class="hero__stat">
            <span class="hero__stat-num">{{ topMasters.length }}+</span>
            <span class="hero__stat-label">Мастеров</span>
          </div>
          <div class="hero__stat-divider" />
          <div class="hero__stat">
            <span class="hero__stat-num">{{ categories.length }}</span>
            <span class="hero__stat-label">Категорий</span>
          </div>
          <div class="hero__stat-divider" />
          <div class="hero__stat">
            <span class="hero__stat-num">8</span>
            <span class="hero__stat-label">Городов</span>
          </div>
          <div class="hero__stat-divider" />
          <div class="hero__stat">
            <span class="hero__stat-num">4.8</span>
            <span class="hero__stat-label">Средний рейтинг</span>
          </div>
        </div>
      </div>

      <div class="hero__scroll">
        <span>Листайте вниз</span>
        <div class="hero__scroll-line" />
      </div>
    </section>

    <!-- Категории -->
    <section class="section">
      <div class="container">
        <div class="section__header">
          <div>
            <span class="section__label">Услуги</span>
            <h2 class="section__title">Какая помощь нужна?</h2>
            <p class="section__desc">Выберите категорию и найдите специалиста</p>
          </div>
        </div>

        <div class="categories" v-if="categories.length">
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="category-card"
            @click="searchByCategory(cat.slug)"
          >
            <span class="category-card__emoji">{{ categoryEmoji[cat.icon] || '🔧' }}</span>
            <span class="category-card__name">{{ cat.name }}</span>
            <span class="category-card__arrow">→</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Топ мастера -->
    <section class="section section--dark">
      <div class="container">
        <div class="section__header section__header--light">
          <div>
            <span class="section__label section__label--light">Лучшие специалисты</span>
            <h2 class="section__title section__title--light">Топ мастера</h2>
          </div>
          <button class="section__link section__link--light" @click="goToSearch">
            Смотреть всех
            <span class="section__link-arrow">→</span>
          </button>
        </div>

        <div class="masters-grid" v-if="topMasters.length">
          <div
            v-for="(master, i) in topMasters"
            :key="master.id"
            class="master-card"
            :style="{ animationDelay: `${i * 0.1}s` }"
            @click="router.push({ name: 'master-profile', params: { id: master.id } })"
          >
            <div class="master-card__ribbon" v-if="i < 3">
              {{ i === 1 ? '🥇' : i === 2 ? '🥈' : '🥉' }}
            </div>

            <div class="master-card__head">
              <div class="master-card__avatar">
                <span>{{ master.firstName[0] }}{{ master.lastName[0] }}</span>
              </div>
              <div class="master-card__info">
                <h3 class="master-card__name">{{ master.firstName }} {{ master.lastName }}</h3>
                <p class="master-card__region">{{ master.regionName || 'Не указан' }}</p>
              </div>
            </div>

            <div class="master-card__rating">
              <div class="master-card__stars">
                <span v-for="s in 5" :key="s" class="star" :class="{ 'star--filled': s <= Math.round(Number(master.rating)) }">★</span>
              </div>
              <span class="master-card__score">{{ Number(master.rating).toFixed(1) }}</span>
              <span class="master-card__count">({{ master.reviewsCount }} отзывов)</span>
            </div>

            <div v-if="master.services.length" class="master-card__services">
              <span
                v-for="service in master.services.slice(0, 3)"
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
          <div>
            <span class="section__label">Преимущества</span>
            <h2 class="section__title">Почему выбирают нас?</h2>
          </div>
        </div>

        <div class="features">
          <div class="feature feature--red">
            <div class="feature__icon">🔍</div>
            <h3 class="feature__title">Быстрый поиск</h3>
            <p class="feature__desc">Находите мастера по региону, рейтингу и типу услуги за секунды</p>
          </div>
          <div class="feature feature--blue">
            <div class="feature__icon">📅</div>
            <h3 class="feature__title">Онлайн-запись</h3>
            <p class="feature__desc">Выберите удобное время и запишитесь без звонков</p>
          </div>
          <div class="feature feature--green">
            <div class="feature__icon">⭐</div>
            <h3 class="feature__title">Реальные отзывы</h3>
            <p class="feature__desc">Читайте честные отзывы реальных клиентов</p>
          </div>
          <div class="feature feature--purple">
            <div class="feature__icon">📍</div>
            <h3 class="feature__title">Геопоиск</h3>
            <p class="feature__desc">Находите ближайших мастеров по вашему местоположению</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Как это работает -->
    <section class="section section--gray">
      <div class="container">
        <div class="section__header">
          <div>
            <span class="section__label">Как это работает</span>
            <h2 class="section__title">Три простых шага</h2>
          </div>
        </div>

        <div class="steps">
          <div class="step">
            <div class="step__num">01</div>
            <div class="step__content">
              <h3 class="step__title">Найдите мастера</h3>
              <p class="step__desc">Введите регион и категорию — система покажет лучших специалистов рядом с вами</p>
            </div>
          </div>
          <div class="step__connector" />
          <div class="step">
            <div class="step__num">02</div>
            <div class="step__content">
              <h3 class="step__title">Выберите время</h3>
              <p class="step__desc">Посмотрите расписание и забронируйте удобный слот онлайн</p>
            </div>
          </div>
          <div class="step__connector" />
          <div class="step">
            <div class="step__num">03</div>
            <div class="step__content">
              <h3 class="step__title">Оплатите онлайн</h3>
              <p class="step__desc">Безопасная оплата через нашу платформу — без риска и мошенничества</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta">
      <div class="cta__bg">
        <div class="cta__orb cta__orb--1" />
        <div class="cta__orb cta__orb--2" />
      </div>
      <div class="container cta__inner">
        <div class="cta__content">
          <h2 class="cta__title">Хотите стать мастером?</h2>
          <p class="cta__desc">Зарегистрируйтесь, создайте профиль и начните получать заказы уже сегодня</p>
          <div class="cta__buttons">
            <button
              v-if="!auth.isAuth"
              class="cta__btn cta__btn--primary"
              @click="router.push({ name: 'register' })"
            >
              Начать бесплатно
            </button>
            <button
              v-else
              class="cta__btn cta__btn--primary"
              @click="router.push({ name: 'master-dashboard' })"
            >
              Перейти в кабинет
            </button>
            <button class="cta__btn cta__btn--ghost" @click="goToSearch">
              Найти мастера
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container footer__inner">
        <div class="footer__brand">
          <span class="footer__logo">A</span>
          <span class="footer__name">Avtomaster.kg</span>
        </div>
        <p class="footer__copy">© 2026 Avtomaster.kg — Платформа автомастеров Кыргызстана</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ─── Base ─── */
.home { min-height: 100vh; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

/* ─── Hero ─── */
.hero {
  position: relative; display: flex; align-items: center; justify-content: center;
  min-height: 100vh; overflow: hidden;
  background: #0a0a1a;
}
.hero__bg {
  position: absolute; inset: 0; overflow: hidden;
}
.hero__orb {
  position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
  animation: float 8s ease-in-out infinite;
}
.hero__orb--1 {
  width: 500px; height: 500px; top: -10%; left: -5%;
  background: radial-gradient(circle, #e63946, transparent 70%);
  animation-delay: 0s;
}
.hero__orb--2 {
  width: 400px; height: 400px; bottom: -10%; right: -5%;
  background: radial-gradient(circle, #3a86ff, transparent 70%);
  animation-delay: -3s;
}
.hero__orb--3 {
  width: 300px; height: 300px; top: 30%; right: 20%;
  background: radial-gradient(circle, #8338ec, transparent 70%);
  animation-delay: -6s;
}
@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.05); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}

.hero__content {
  position: relative; z-index: 1; text-align: center; color: white;
  max-width: 780px; padding: 2rem;
  animation: fadeInUp 0.8s ease-out;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.hero__badge {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 8px 20px; border-radius: 50px;
  background: rgba(255,255,255,0.08); backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.1);
  font-size: 0.85rem; font-weight: 600; letter-spacing: 0.5px;
  margin-bottom: 2rem;
}
.hero__badge-dot {
  width: 8px; height: 8px; border-radius: 50%; background: #4ade80;
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

.hero__title {
  font-size: 4rem; font-weight: 900; line-height: 1.05; margin: 0 0 1.25rem;
  letter-spacing: -1.5px;
}
.hero__title--accent {
  background: linear-gradient(135deg, #e63946 0%, #ff6b6b 50%, #ffa500 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero__subtitle {
  font-size: 1.15rem; color: rgba(255,255,255,0.6); margin: 0 0 2.5rem;
  line-height: 1.7; font-weight: 400;
}

.hero__search {
  display: flex; align-items: center; gap: 0;
  max-width: 600px; margin: 0 auto 1.5rem;
  background: rgba(255,255,255,0.1); backdrop-filter: blur(20px);
  border-radius: 16px; border: 1px solid rgba(255,255,255,0.12);
  padding: 6px; transition: all 0.3s;
}
.hero__search:focus-within {
  border-color: rgba(230,57,70,0.5);
  box-shadow: 0 0 0 4px rgba(230,57,70,0.1);
}
.hero__search-icon {
  padding: 0 12px; font-size: 1.2rem; opacity: 0.6;
}
.hero__input {
  flex: 1; padding: 14px 0; border: none; background: transparent;
  color: white; font-size: 1rem; outline: none;
}
.hero__input::placeholder { color: rgba(255,255,255,0.35); }
.hero__search-btn {
  padding: 14px 28px; background: linear-gradient(135deg, #e63946, #d32f3f);
  color: white; border: none; border-radius: 12px;
  font-size: 0.95rem; font-weight: 700; cursor: pointer;
  transition: all 0.2s; white-space: nowrap;
}
.hero__search-btn:hover {
  transform: scale(1.03);
  box-shadow: 0 4px 20px rgba(230,57,70,0.4);
}

.hero__tags {
  display: flex; justify-content: center; flex-wrap: wrap; gap: 8px;
  margin-bottom: 3rem;
}
.hero__tag {
  padding: 6px 16px; border-radius: 50px;
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
  color: rgba(255,255,255,0.5); font-size: 0.85rem; font-weight: 500;
  cursor: pointer; transition: all 0.2s;
}
.hero__tag:hover {
  background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.8);
  border-color: rgba(255,255,255,0.2);
}

.hero__stats {
  display: flex; align-items: center; justify-content: center; gap: 2rem;
}
.hero__stat { text-align: center; }
.hero__stat-num {
  display: block; font-size: 2rem; font-weight: 800;
  background: linear-gradient(135deg, #fff, rgba(255,255,255,0.7));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.hero__stat-label { font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-top: 2px; }
.hero__stat-divider {
  width: 1px; height: 40px; background: rgba(255,255,255,0.1);
}

.hero__scroll {
  position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  color: rgba(255,255,255,0.3); font-size: 0.75rem; letter-spacing: 1px;
  text-transform: uppercase;
}
.hero__scroll-line {
  width: 1px; height: 30px; background: rgba(255,255,255,0.2);
  animation: scrollLine 2s ease-in-out infinite;
}
@keyframes scrollLine {
  0%, 100% { opacity: 0.2; height: 30px; }
  50% { opacity: 0.6; height: 50px; }
}

/* ─── Sections ─── */
.section { padding: 6rem 0; }
.section--gray { background: #f8f9fb; }
.section--dark { background: #0f0f23; }
.section__header {
  display: flex; justify-content: space-between; align-items: flex-end;
  margin-bottom: 3rem; flex-wrap: wrap; gap: 1rem;
}
.section__header--light { color: white; }
.section__label {
  display: inline-block; padding: 4px 12px; border-radius: 6px;
  background: #fef2f2; color: #e63946; font-size: 0.75rem;
  font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
  margin-bottom: 0.75rem;
}
.section__label--light { background: rgba(230,57,70,0.15); }
.section__title {
  font-size: 2.2rem; font-weight: 800; color: #0f0f23; margin: 0;
  letter-spacing: -0.5px;
}
.section__title--light { color: white; }
.section__desc {
  color: #888; margin: 0.5rem 0 0; font-size: 1.05rem; line-height: 1.5;
}
.section__link {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 20px; background: white; border: 2px solid #e63946;
  border-radius: 10px; color: #e63946; font-size: 0.9rem;
  font-weight: 700; cursor: pointer; transition: all 0.2s;
  text-decoration: none;
}
.section__link:hover { background: #e63946; color: white; }
.section__link--light {
  background: transparent; border-color: rgba(255,255,255,0.2);
  color: white;
}
.section__link--light:hover {
  background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.4);
}
.section__link-arrow { font-size: 1.1rem; transition: transform 0.2s; }
.section__link:hover .section__link-arrow { transform: translateX(4px); }

/* ─── Категории ─── */
.categories {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}
.category-card {
  display: flex; align-items: center; gap: 1rem;
  padding: 1.25rem 1.5rem; background: white;
  border: 1.5px solid #f0f0f0; border-radius: 14px;
  cursor: pointer; transition: all 0.25s;
}
.category-card:hover {
  border-color: #e63946; transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(230,57,70,0.1);
}
.category-card__emoji { font-size: 1.8rem; }
.category-card__name {
  flex: 1; font-weight: 700; font-size: 0.95rem; color: #1a1a2e;
  text-align: left;
}
.category-card__arrow {
  color: #ccc; font-size: 1.1rem; transition: all 0.2s;
}
.category-card:hover .category-card__arrow {
  color: #e63946; transform: translateX(4px);
}

/* ─── Топ мастера ─── */
.masters-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.25rem;
}
.master-card {
  position: relative; background: rgba(255,255,255,0.05);
  backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px; padding: 1.5rem; cursor: pointer;
  transition: all 0.3s; animation: fadeInUp 0.6s ease-out both;
}
.master-card:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(230,57,70,0.3);
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.master-card__ribbon {
  position: absolute; top: 12px; right: 12px; font-size: 1.3rem;
}
.master-card__head {
  display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;
}
.master-card__avatar {
  width: 52px; height: 52px; border-radius: 14px;
  background: linear-gradient(135deg, #e63946, #ff6b6b);
  color: white; font-weight: 800; font-size: 1rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.master-card__info { flex: 1; min-width: 0; }
.master-card__name {
  margin: 0; font-size: 1.1rem; color: white; font-weight: 700;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.master-card__region {
  margin: 2px 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.4);
}
.master-card__rating {
  display: flex; align-items: center; gap: 6px; margin-bottom: 1rem;
}
.master-card__stars { display: flex; gap: 1px; }
.star { color: rgba(255,255,255,0.15); font-size: 0.85rem; }
.star--filled { color: #f59e0b; }
.master-card__score {
  font-weight: 800; color: #f59e0b; font-size: 0.95rem;
}
.master-card__count {
  color: rgba(255,255,255,0.3); font-size: 0.8rem;
}
.master-card__services { display: flex; flex-wrap: wrap; gap: 6px; }
.master-card__service {
  padding: 4px 10px; background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 8px;
  font-size: 0.75rem; color: rgba(255,255,255,0.5);
}

/* ─── Преимущества ─── */
.features {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
}
.feature {
  padding: 2rem; background: white; border-radius: 20px;
  border: 1.5px solid #f0f0f0; transition: all 0.3s;
  position: relative; overflow: hidden;
}
.feature:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.06);
}
.feature__icon {
  width: 56px; height: 56px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; margin-bottom: 1.25rem;
}
.feature--red .feature__icon { background: #fef2f2; }
.feature--blue .feature__icon { background: #eff6ff; }
.feature--green .feature__icon { background: #f0fdf4; }
.feature--purple .feature__icon { background: #f5f3ff; }
.feature__title {
  margin: 0 0 0.5rem; font-size: 1.1rem; color: #0f0f23; font-weight: 700;
}
.feature__desc {
  margin: 0; color: #888; font-size: 0.9rem; line-height: 1.6;
}

/* ─── Steps ─── */
.steps {
  display: flex; align-items: center; gap: 0; flex-wrap: wrap; justify-content: center;
}
.step {
  flex: 1; min-width: 250px; max-width: 320px; text-align: center; padding: 2rem 1.5rem;
}
.step__num {
  width: 64px; height: 64px; margin: 0 auto 1.25rem;
  border-radius: 20px; background: linear-gradient(135deg, #e63946, #ff6b6b);
  color: white; font-weight: 900; font-size: 1.2rem;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 8px 24px rgba(230,57,70,0.25);
}
.step__title {
  margin: 0 0 0.5rem; font-size: 1.1rem; color: #0f0f23; font-weight: 700;
}
.step__desc {
  margin: 0; color: #888; font-size: 0.9rem; line-height: 1.6;
}
.step__connector {
  width: 60px; height: 2px; background: linear-gradient(90deg, #e63946, #ff6b6b);
  opacity: 0.3; flex-shrink: 0; margin: 0 -20px;
}

/* ─── CTA ─── */
.cta {
  position: relative; padding: 5rem 0; overflow: hidden;
}
.cta__bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
}
.cta__orb {
  position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.3;
}
.cta__orb--1 {
  width: 300px; height: 300px; top: -20%; right: 10%;
  background: radial-gradient(circle, #e63946, transparent 70%);
}
.cta__orb--2 {
  width: 200px; height: 200px; bottom: -30%; left: 15%;
  background: radial-gradient(circle, #3a86ff, transparent 70%);
}
.cta__inner { position: relative; z-index: 1; }
.cta__content { text-align: center; color: white; }
.cta__title {
  font-size: 2.5rem; font-weight: 900; margin: 0 0 1rem;
  letter-spacing: -1px;
}
.cta__desc {
  margin: 0 0 2rem; font-size: 1.1rem; color: rgba(255,255,255,0.6);
  max-width: 500px; margin-left: auto; margin-right: auto; line-height: 1.6;
}
.cta__buttons { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
.cta__btn {
  padding: 14px 32px; border-radius: 12px; font-size: 1rem;
  font-weight: 700; cursor: pointer; transition: all 0.25s; border: none;
}
.cta__btn--primary {
  background: linear-gradient(135deg, #e63946, #d32f3f); color: white;
}
.cta__btn--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(230,57,70,0.4);
}
.cta__btn--ghost {
  background: rgba(255,255,255,0.08); color: white;
  border: 1.5px solid rgba(255,255,255,0.2);
}
.cta__btn--ghost:hover {
  background: rgba(255,255,255,0.15);
  border-color: rgba(255,255,255,0.3);
}

/* ─── Footer ─── */
.footer {
  padding: 2rem 0; background: #0a0a1a; border-top: 1px solid rgba(255,255,255,0.05);
}
.footer__inner {
  display: flex; justify-content: space-between; align-items: center;
  flex-wrap: wrap; gap: 1rem;
}
.footer__brand {
  display: flex; align-items: center; gap: 10px;
}
.footer__logo {
  width: 32px; height: 32px; border-radius: 8px;
  background: linear-gradient(135deg, #1a1a2e, #e63946);
  color: white; font-weight: 800; font-size: 0.9rem;
  display: flex; align-items: center; justify-content: center;
}
.footer__name { color: rgba(255,255,255,0.6); font-weight: 600; font-size: 0.9rem; }
.footer__copy { margin: 0; color: rgba(255,255,255,0.25); font-size: 0.8rem; }

/* ─── Responsive ─── */
@media (max-width: 768px) {
  .hero__title { font-size: 2.5rem; }
  .hero__search { flex-direction: column; gap: 8px; padding: 8px; }
  .hero__search-icon { display: none; }
  .hero__search-btn { width: 100%; text-align: center; }
  .hero__stats { gap: 1rem; }
  .hero__stat-num { font-size: 1.3rem; }
  .hero__stat-divider { display: none; }
  .hero__scroll { display: none; }
  .section { padding: 4rem 0; }
  .section__title { font-size: 1.6rem; }
  .masters-grid { grid-template-columns: 1fr; }
  .categories { grid-template-columns: 1fr 1fr; }
  .steps { flex-direction: column; }
  .step__connector { width: 2px; height: 30px; margin: 0; }
  .cta__title { font-size: 1.8rem; }
  .footer__inner { flex-direction: column; text-align: center; }
}
</style>
