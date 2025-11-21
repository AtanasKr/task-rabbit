<template>
  <div class="dashboard-info">
    <header class="top-bar">
      <div>
        <h1>{{ authState.user.name.charAt(0).toUpperCase() + authState.user.name.slice(1) }}'s Dashboard</h1>
        <p v-if="authState.user">
          Hello, <strong>{{ authState.user.name.charAt(0).toUpperCase() + authState.user.name.slice(1) }}</strong><br>
        <p>You are curretnly logged as {{ authState.user.role }}.</p>
        </p>
      </div>
    </header>

    <main>
      <p>
        Hope you have a great day! Lets see what you have scheduled!
      </p>
    </main>
  </div>
  <div class="tasks">
  </div>
</template>

<script setup>
import { authState } from '../auth';

</script>

<style scoped>
.dashboard-info {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 70rem;
  margin: 0 auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

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