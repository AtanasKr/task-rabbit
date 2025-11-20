<template>
  <div class="auth-page">
    <h1>Task Rabbit</h1>
    <img :src="logoMain" alt="Task Rabbit logo" class="logo" />

    <form @submit.prevent="handleLogin">
      <div class="field">
        <input v-model="form.email" type="email" required placeholder=" " />
        <label>Email</label>
      </div>

      <div class="field password-field">
        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder=" " />
        <label>Password</label>

        <button type="button" class="eye-toggle" @click="showPassword = !showPassword"
          :aria-label="showPassword ? 'Hide password' : 'Show password'">
          <span class="eye" :class="{ 'eye--open': showPassword }"></span>
        </button>
      </div>

      <p v-if="authState.error" class="error">
        {{ authState.error }}
      </p>

      <button type="submit" :disabled="authState.loading">
        {{ authState.loading ? 'Logging in...' : 'Login' }}
      </button>
    </form>

    <p class="switch-link">
      No account?
      <router-link to="/register">Register here</router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { authState, login } from '../auth';

import logoMain from '../assets/images/logo-main.png';

const router = useRouter();

const form = reactive({
  email: '',
  password: '',
});

const showPassword = ref(false);

const handleLogin = async () => {
  const ok = await login(form);
  if (ok) {
    router.push({ name: 'dashboard' });
  }
};
</script>

<style scoped>
.auth-page {
  text-align: center;
}

.logo {
  display: block;
  max-width: 160px;
  margin: 0.5rem auto 1.5rem;
}

.field {
  position: relative;
  margin-bottom: 1.5rem;
}

.field input {
  width: 100%;
  padding: 0.75rem 0.75rem;
  border-radius: 4px;
  border: 1px solid #ddd;
  font-size: 1rem;
  outline: none;
  box-sizing: border-box;
}

.field input:focus {
  border-color: #2563eb;
}

.field input::placeholder {
  color: transparent;
}

.field label {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.95rem;
  color: #888;
  pointer-events: none;
  transition: 0.15s ease;
  background: #fff;
  padding: 0 0.2rem;
}

.field input:focus+label,
.field input:not(:placeholder-shown)+label {
  top: -0.5rem;
  font-size: 0.75rem;
  color: #2563eb;
}

.password-field {
  position: relative;
  margin-bottom: 1.5rem;
}

.eye-toggle {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.eye {
  width: 22px;
  height: 12px;
  border-radius: 999px;
  border: 2px solid #888;
  position: relative;
  transition: all 0.18s ease;
}

.eye::before {
  content: '';
  position: absolute;
  left: 50%;
  top: 50%;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #888;
  transform: translate(-50%, -50%) scale(0.7);
  transition: transform 0.18s ease, background 0.18s ease;
}

.eye::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  top: 50%;
  height: 2px;
  background: #888;
  transform: translateY(-50%) rotate(45deg);
  opacity: 1;
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.eye--open {
  border-color: #2563eb;
}

.eye--open::before {
  background: #2563eb;
  transform: translate(-50%, -50%) scale(1);
}

.eye--open::after {
  opacity: 0;
  transform: translateY(-50%) rotate(0deg);
}

button[type='submit'] {
  width: 100%;
  padding: 0.7rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background: #3b82f6;
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
  transition: background 0.15s ease, transform 0.05s ease;
}

button[type='submit']:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-1px);
}

button[type='submit']:disabled {
  background: #9ca3af;
  cursor: default;
}

.error {
  color: #c00;
  margin-bottom: 0.5rem;
  text-align: left;
}

.switch-link {
  margin-top: 1rem;
  text-align: center;
}
</style>