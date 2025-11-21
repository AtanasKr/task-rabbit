<template>
    <div class="layout">
        <aside class="sidebar">
            <div class="header">
                <img :src="logoMini" alt="Task Rabbit logo" class="logo" width="55px" height="55px" />
                <h2>Task Rabbit</h2>
            </div>
            <nav>
                <router-link to="/">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </router-link>
                <router-link to="/profile">
                    <i class="fas fa-users"></i>
                    Manage Users
                </router-link>
                <router-link to="/settings">
                    <i class="fas fa-project-diagram"></i>
                    Manage Projects
                </router-link>
                <router-link to="/settings">
                    <i class="fas fa-chart-bar"></i>
                    Analytics
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
            <router-view />
        </main>
    </div>
</template>

<script setup>
import logoMini from '../assets/images/logo-mini.png';
import { useRouter } from 'vue-router';
import { logout } from '../auth';
import { startLoading, stopLoading } from '../stores/loading';

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
}

.layout {
    display: flex;
    height: 100vh;
    width: 100vw;
    position: fixed;
    top: 0;
    left: 0;
}

.sidebar {
    width: 220px;
    background: #2d2f3a;
    color: white;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    /* Ensures logout is at bottom */
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
    /* Space between icon and text */
}

.sidebar a.router-link-active {
    background: #44475a;
}

.logout {
    margin-top: auto;
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
    padding: 2rem;
    background: #f5f5f5;
}
</style>
