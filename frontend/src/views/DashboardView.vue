<template>
  <div class="dashboard-page">
    <header class="top-bar">
      <div>
        <h1>Dashboard</h1>
        <p v-if="authState.user">
          Hello, <strong>{{ authState.user.name }}</strong>
        </p>
      </div>
      <button @click="handleLogout">Logout</button>
    </header>

    <main>
      <p>
        Protected page. Later we’ll add your task management stuff here.
      </p>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { authState, logout } from '../auth';
import { startLoading, stopLoading } from '../stores/loading';

const router = useRouter();

const handleLogout = async () => {
  startLoading();
  await logout();
  stopLoading();
  router.push({ name: 'login' });S
};
</script>

<style scoped>
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

button {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}
</style>