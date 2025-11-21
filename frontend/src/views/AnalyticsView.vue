<template>
  <div class="analytics-page">
    <header class="top-bar">
      <div>
        <h1>Analytics</h1>
        <p v-if="authState.user">
          Hello, <strong>{{ authState.user.name }}</strong>
        </p>
      </div>
    </header>

    <main>
      <p>
        Protected page. Later we’ll add your task management stuff here.
      </p>
    </main>
  </div>
</template>

<script setup>
import { authState} from '../auth';

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