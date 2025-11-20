import { reactive } from 'vue';
import api from './api';

export const authState = reactive({
  user: null,
  token: localStorage.getItem('auth_token') || null,
  loading: false,
  error: null,
});

export async function fetchUser() {
  if (!authState.token) return;
  try {
    authState.loading = true;
    const { data } = await api.get('/user');
    authState.user = data;
  } catch (e) {
    authState.user = null;
    authState.token = null;
    localStorage.removeItem('auth_token');
  } finally {
    authState.loading = false;
  }
}

export async function login(payload) {
  authState.error = null;
  authState.loading = true;

  try {
    const { data } = await api.post('/login', payload);
    authState.token = data.token;
    authState.user = data.user;
    localStorage.setItem('auth_token', data.token);
    return true;
  } catch (e) {
    authState.error =
      e.response?.data?.message ||
      'Login failed. Check your email and password.';
    return false;
  } finally {
    authState.loading = false;
  }
}

export async function register(payload) {
  authState.error = null;
  authState.loading = true;

  try {
    const { data } = await api.post('/register', payload);
    authState.token = data.token;
    authState.user = data.user;
    localStorage.setItem('auth_token', data.token);
    return true;
  } catch (e) {
    // Laravel validation errors
    if (e.response?.data?.errors) {
      authState.error = Object.values(e.response.data.errors)
        .flat()
        .join(' ');
    } else {
      authState.error = 'Register failed.';
    }
    return false;
  } finally {
    authState.loading = false;
  }
}

export async function logout() {
  try {
    await api.post('/logout');
  } catch (e) {
    console.error('Logout failed', e);
  } finally {
    authState.user = null;
    authState.token = null;
    localStorage.removeItem('auth_token');
  }
}
