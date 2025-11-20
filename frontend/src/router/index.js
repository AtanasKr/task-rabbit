import { createRouter, createWebHistory } from 'vue-router';
import { authState, fetchUser } from '../auth';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import DashboardView from '../views/DashboardView.vue';

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { guestOnly: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  // if we have token but no user, fetch user
  if (authState.token && !authState.user) {
    await fetchUser();
  }

  if (to.meta.requiresAuth && !authState.token) {
    return next({ name: 'login' });
  }

  if (to.meta.guestOnly && authState.token) {
    return next({ name: 'dashboard' });
  }

  next();
});

export default router;