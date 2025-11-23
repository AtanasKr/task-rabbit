<template>
  <div class="dashboard-container">
    <div class="dashboard-info">
      <header class="top-bar">
        <div>
          <h1>{{ formattedName }}'s Dashboard</h1>
          <p>Hello, <strong>{{ formattedName }}</strong></p>
          <p>You are currently logged in as {{ authState.user.role }}.</p>
        </div>
      </header>

      <main>
        <p>Hope you have a great day! Let's see what you have scheduled!</p>
      </main>
    </div>

    <TasksSection />
  </div>
</template>

<script setup>
import { authState } from "../auth";
import TasksSection from "../components/TasksSection.vue";

const formattedName =
  authState.user.name.charAt(0).toUpperCase() +
  authState.user.name.slice(1);
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
</style>