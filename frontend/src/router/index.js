import { createRouter, createWebHistory } from 'vue-router';
import { authState, fetchUser } from '../auth';

import SidebarLayout from '../layouts/SidebarLayout.vue';
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';

const routes = [
  {
    path: '/',
    component: SidebarLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: DashboardView,
      },
      {
        path: 'manage-users',
        name: 'manage-users',
        component: () => import('../views/ManageUsersView.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'manage-projects',
        name: 'manage-projects',
        component: () => import('../views/ManageProjectsView.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'analytics',
        name: 'analytics',
        component: () => import('../views/AnalyticsView.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'create-task',
        name: 'create-task',
        component: () => import('../views/CreateTaskView.vue'),
      },
      {
        path: '/tasks/:id',
        name: 'task-detail',
        component: () => import('../views/TaskDetailView.vue'),
      },
      {
        path: 'task-manager',
        name: 'task-manager',
        component: () => import('../views/TaskManager.vue'),
      }
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

  if (to.meta.requiresAuth && !authState.token) {
    return next({ name: 'login' });
  }

  if (to.meta.guestOnly && authState.token) {
    return next({ name: 'dashboard' });
  }

  if (to.meta.requiresAdmin) {
    if (!authState.user || authState.user.role !== 'admin') {
      return next({ name: 'dashboard' });
    }
  }

  next();
});

export default router;
