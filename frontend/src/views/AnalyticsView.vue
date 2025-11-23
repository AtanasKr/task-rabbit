<template>
  <div class="analytics-page">
    <header class="top-bar">
      <div>
        <h1>Analytics</h1>
      </div>
    </header>

    <ErrorAlertComponent :message="errorMessage" :field-errors="errors" @close="clearErrors" />

    <main>
      <div class="cards-row">
        <div class="card">
          <h3>All Tasks</h3>
          <p>{{ stats.allTasks }}</p>
        </div>
        <div class="card">
          <h3>All Projects</h3>
          <p>{{ stats.allProjects }}</p>
        </div>
        <div class="card">
          <h3>All Users</h3>
          <p>{{ stats.allUsers }}</p>
        </div>
      </div>

      <div class="cards-row">
        <div class="card">
          <h3>Completed Tasks</h3>
          <p>{{ stats.completedTasks }}</p>
          <div class="progress-bar completed">
            <div class="progress" :style="{ width: completedPercent + '%' }"></div>
          </div>
        </div>
        <div class="card">
          <h3>Closed Tasks</h3>
          <p>{{ stats.closedTasks }}</p>
          <div class="progress-bar closed">
            <div class="progress" :style="{ width: closedPercent + '%' }"></div>
          </div>
        </div>
        <div class="card">
          <h3>In Progress Tasks</h3>
          <p>{{ stats.inProgressTasks }}</p>
          <div class="progress-bar in-progress">
            <div class="progress" :style="{ width: inProgressPercent + '%' }"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import axiosInstance from "../api/axios";
import { startLoading, stopLoading } from '../stores/loading';
import ErrorAlertComponent from '../components/ErrorAlertComponent.vue';
import { useErrorHandler } from '../composables/userErrorHandler';

const stats = ref({
  allTasks: 0,
  completedTasks: 0,
  closedTasks: 0,
  inProgressTasks: 0,
  allProjects: 0,
  allUsers: 0,
});

const { errors, errorMessage, handleError, clearErrors } = useErrorHandler();

const fetchStats = async () => {
  startLoading();
  try {
    const response = await axiosInstance.get('/api/analytics');
    const data = response.data;

    stats.value.allTasks = data.tasks.all;
    stats.value.completedTasks = data.tasks.completed;
    stats.value.closedTasks = data.tasks.closed;
    stats.value.inProgressTasks = data.tasks.in_progress;
    stats.value.allProjects = data.projects;
    stats.value.allUsers = data.users;
  } catch (error) {
    handleError(error);
  } finally {
    stopLoading();
  }
};

const completedPercent = computed(() =>
  stats.value.allTasks ? (stats.value.completedTasks / stats.value.allTasks) * 100 : 0
);
const closedPercent = computed(() =>
  stats.value.allTasks ? (stats.value.closedTasks / stats.value.allTasks) * 100 : 0
);
const inProgressPercent = computed(() =>
  stats.value.allTasks ? (stats.value.inProgressTasks / stats.value.allTasks) * 100 : 0
);

onMounted(() => {
  fetchStats();
});
</script>

<style scoped>
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 0 2rem;
}

.cards-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 0 2rem;
}

.card {
  background: #fff;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.card h3 {
  margin-bottom: 0.5rem;
}

.progress-bar {
  height: 10px;
  background-color: #eee;
  border-radius: 5px;
  margin-top: 0.5rem;
  overflow: hidden;
}

.progress {
  height: 100%;
  border-radius: 5px;
}

.progress-bar.completed .progress {
  background-color: #4caf50;
}

.progress-bar.closed .progress {
  background-color: #f44336;
}

.progress-bar.in-progress .progress {
  background-color: #ff9800;
}
</style>
