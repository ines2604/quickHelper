import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: () => import('../views/login.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('../views/register.vue'), meta: { guest: true } },
  { path: '/verify-email', component: () => import('../views/verifyemail.vue'), meta: { guest: true } },
  { path: '/forgot-password', component: () => import('../views/forgotpassword.vue'), meta: { guest: true } },
  { path: '/reset-password', component: () => import('../views/resetpassword.vue'), meta: { guest: true } },
  { path: '/dashboard', component: () => import('../views/dashboard.vue'), meta: { requiresAuth: true } },
  { path: '/requests', component: () => import('../views/requestlist.vue'), meta: { requiresAuth: true } },
  { path: '/requests/create', component: () => import('../views/createrequest.vue'), meta: { requiresAuth: true } },
  { path: '/requests/:id', component: () => import('../views/requestdetail.vue'), meta: { requiresAuth: true } },
  { path: '/requests/:id/edit', component: () => import('../views/editrequest.vue'), meta: { requiresAuth: true } },
  { path: '/profile', component: () => import('../views/profile.vue'), meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  if (authStore.token && !authStore.user) {
    await authStore.fetchUser();
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login');
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    return next('/dashboard');
  }

  next();
});

export default router;
