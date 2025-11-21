import { createRouter, createWebHistory } from 'vue-router';
import { authState, fetchUser } from '../auth';

import DashboardLayout from '../layouts/DashboardLayout.vue';
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';

const routes = [
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView,
      },
      {
        path: 'manage-users',
        name: 'manage-users',
        component: () => import('../views/ManageUsersView.vue'),
      },
      {
        path: 'manage-projects',
        name: 'manage-projects',
        component: () => import('../views/ManageProjectsView.vue'),
      },
      {
        path: 'analytics',
        name: 'analytics',
        component: () => import('../views/AnalyticsView.vue'),
      },
    ],
  },

  // Auth routes (NO SIDEBAR)
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
  if (authState.token && !authState.user) {
    await fetchUser();
  }
  if (to.meta.requiresAuth && !authState.token) return next({ name: 'login' });
  if (to.meta.guestOnly && authState.token) return next({ name: 'dashboard' });

  next();
});

export default router;
