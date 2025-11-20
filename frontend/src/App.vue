<template>
  <div id="app">
    <GlobalLoader />
    <router-view />
  </div>
</template>

<script setup>
import GlobalLoader from './components/GlobalLoader.vue';
</script>

<style>
body {
  margin: 0;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI',
    sans-serif;
}

#app {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
}

.auth-page,
.dashboard-page {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  min-width: 320px;
  max-width: 480px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
</style>
