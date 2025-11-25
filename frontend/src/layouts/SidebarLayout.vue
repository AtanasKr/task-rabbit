<template>
    <div class="layout">
        <aside class="sidebar">
            <div class="header">
                <img :src="logoMini" alt="Task Rabbit logo" class="logo" width="55px" height="55px" />
                <h2>Task Rabbit</h2>
            </div>
            <nav>
                <router-link to="/dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </router-link>
                <router-link v-if="authState.user.role==='admin'" to="/manage-users">
                    <i class="fas fa-users"></i>
                    Manage Users
                </router-link>
                <router-link v-if="authState.user.role==='admin'" to="/manage-projects">
                    <i class="fas fa-project-diagram"></i>
                    Manage Projects
                </router-link>
                <router-link v-if="authState.user.role==='admin'" to="/analytics">
                    <i class="fas fa-chart-bar"></i>
                    Analytics
                </router-link>
                <router-link to="/task-manager">
                    <i class="fas fa-project-diagram"></i>
                    Task Manager
                </router-link>
                <router-link to="/create-task">
                    <i class="fas fa-plus-circle"></i>
                    Create task
                </router-link>
            </nav>

            <div class="logout">
                <button @click="handleLogout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </aside>

        <main class="content">
            <div class="content-wrapper">
                <router-view />
            </div>
        </main>
    </div>
</template>

<script setup>
import logoMini from '../assets/images/logo-mini.png';
import { useRouter } from 'vue-router';
import { logout } from '../auth';
import { startLoading, stopLoading } from '../stores/loading';
import { authState } from '../auth';

const router = useRouter();

const handleLogout = async () => {
  startLoading();
  await logout();
  stopLoading();
  router.push({ name: 'login' });
};
</script>

<style scoped>
.header {
    display: flex;
    margin-top: 1em;
}

.layout {
    display: flex;
    min-height: 100vh;
    width: 100vw;
}

.sidebar {
    width: 220px;
    background: #2d2f3a;
    color: white;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: sticky;
    top: 0;
    height: 100vh;
    padding-top: 0;
    padding-bottom: 0;
}

.sidebar nav {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.sidebar a {
    color: white;
    text-decoration: none;
    padding: 0.5rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar a.router-link-active {
    background: #44475a;
}

.logout {
    margin-top: auto;
    margin-bottom: 1em;
}

.logout button {
    width: 100%;
    background: #ff5555;
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.logout button:hover {
    background: #ff4444;
}

.content {
    flex: 1;
    background: #f5f5f5;
}

.content-wrapper {
    padding-top: 2rem;
    padding-bottom: 2rem;
    min-height: 100vh;
    overflow-y: auto;
}
</style>