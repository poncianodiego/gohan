import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/auth/RegisterView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/DashboardView.vue'),
    meta: { auth: true },
  },
  {
    path: '/servers/create',
    name: 'server-create',
    component: () => import('../views/servers/ServerCreateView.vue'),
    meta: { auth: true },
  },
  {
    path: '/servers/:id',
    name: 'server-detail',
    component: () => import('../views/servers/ServerDetailView.vue'),
    meta: { auth: true },
  },
  {
    path: '/servers/:serverId/sites/create',
    name: 'site-create',
    component: () => import('../views/sites/SiteCreateView.vue'),
    meta: { auth: true },
  },
  {
    path: '/sites/:id',
    name: 'site-detail',
    component: () => import('../views/sites/SiteDetailView.vue'),
    meta: { auth: true },
  },
  {
    path: '/sites/:id/logs',
    name: 'site-logs',
    component: () => import('../views/sites/SiteLogsView.vue'),
    meta: { auth: true },
  },
  {
    path: '/sites/:id/env',
    name: 'site-env',
    component: () => import('../views/sites/SiteEnvView.vue'),
    meta: { auth: true },
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('../views/settings/SettingsView.vue'),
    meta: { auth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.auth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router
