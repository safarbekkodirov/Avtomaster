import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../pages/Home.vue'),
    },
    {
      path: '/search',
      name: 'search',
      component: () => import('../pages/SearchPage.vue'),
    },
    {
      path: '/master/:id',
      name: 'master-profile',
      component: () => import('../pages/MasterProfilePage.vue'),
    },
    {
      path: '/booking/:id',
      name: 'booking',
      component: () => import('../pages/BookingPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../pages/LoginPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../pages/RegisterPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../pages/DashboardClientPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/payment/:bookingId',
      name: 'payment',
      component: () => import('../pages/PaymentPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/master-dashboard',
      name: 'master-dashboard',
      component: () => import('../pages/DashboardMasterPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/notifications',
      name: 'notifications',
      component: () => import('../pages/NotificationsPage.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('access_token')
  if (to.meta.requiresAuth && !token) return { name: 'login' }
  if (to.meta.guest && token) return { name: 'dashboard' }
})

export default router
