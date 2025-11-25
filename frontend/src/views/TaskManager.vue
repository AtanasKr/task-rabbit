<template>
    <div class="tasks-list">
        <div class="tasks-header">
            <h2>Tasks</h2>
            <div class="search-box">
                <input v-model="search" type="text" placeholder="Search tasks..." @input="onSearchInput"
                    class="search-input" />
                <span class="search-icon">🔍</span>
            </div>
        </div>

        <div v-if="loading" class="tasks-state">
            Loading tasks...
        </div>

        <div v-else-if="error" class="tasks-state tasks-state-error">
            {{ error }}
        </div>

        <div v-else-if="!tasks.length" class="tasks-state">
            No tasks found.
        </div>

        <ul v-else class="tasks-items">
            <li v-for="task in tasks" :key="task.id" class="tasks-item">
                <RouterLink :to="{ name: 'task-detail', params: { id: task.id } }" class="tasks-link">
                    <div class="tasks-title">
                        {{ task.title }}
                    </div>

                    <div class="tasks-meta">
                        <span v-if="task.project">
                            📁 {{ task.project.name }}
                        </span>

                        <span v-if="task.status">
                            📌 {{ task.status.name }}
                        </span>

                        <span v-if="task.due_date">
                            ⏰ {{ formatDate(task.due_date) }}
                        </span>

                        <span v-if="task.assignee">
                            👤 Assigned to: {{ task.assignee.name }}
                        </span>

                        <span v-if="task.creator">
                            ✍️ Created by: {{ task.creator.name }}
                        </span>
                    </div>
                </RouterLink>
            </li>
        </ul>
        <div v-if="lastPage > 1" class="tasks-pagination">
            <button class="tasks-page-btn" :disabled="currentPage === 1 || loading" @click="goToPage(currentPage - 1)">
                ‹ Prev
            </button>

            <span class="tasks-page-info">
                Page {{ currentPage }} of {{ lastPage }}
                <span v-if="total !== null"> ({{ total }} tasks)</span>
            </span>

            <button class="tasks-page-btn" :disabled="currentPage === lastPage || loading"
                @click="goToPage(currentPage + 1)">
                Next ›
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import axiosInstance from '../api/axios';

const tasks = ref([]);
const loading = ref(false);
const error = ref(null);
const search = ref('');

const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(null);
const perPage = ref(10);

let searchTimeout = null;

const fetchTasks = async () => {
    loading.value = true;
    error.value = null;

    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
            paginate: true,
        };

        if (search.value) {
            params.search = search.value;
        }

        const response = await axiosInstance.get('/api/tasks', { params });
        const payload = response.data;

        tasks.value = payload.data || [];
        currentPage.value = payload.current_page ?? 1;
        lastPage.value = payload.last_page ?? 1;
        total.value = payload.total ?? tasks.value.length;
        perPage.value = payload.per_page ?? perPage.value;
    } catch (e) {
        console.error(e);
        error.value = 'Could not load tasks.';
    } finally {
        loading.value = false;
    }
};

const onSearchInput = () => {
    currentPage.value = 1;

    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchTasks();
    }, 400);
};

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    currentPage.value = page;
    fetchTasks();
};

const formatDate = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleDateString();
};

onMounted(() => {
    fetchTasks();
});
</script>

<style scoped>
.tasks-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    width: 100%;
}

.tasks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    width: 100%;
    max-width: 50rem;
}

.search-box {
    position: relative;
    width: 200px;
    padding-right: 1em;
}

.search-input {
    width: 100%;
    padding: 0.75rem 0.5rem;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.search-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}

.tasks-state {
    font-size: 0.9rem;
    color: #555;
}

.tasks-state-error {
    color: #b00020;
}

.tasks-items {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 100%;
    max-width: 55rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.tasks-item {
    width: 100%;
    border-radius: 6px;
    border: 1px solid #eee;
    background: #fff;
    transition: box-shadow 0.15s ease, transform 0.05s ease;
}

.tasks-link {
    display: block;
    padding: 0.75rem 0.9rem;
    text-decoration: none;
    color: inherit;
}

.tasks-item:hover {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}

.tasks-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.tasks-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #666;
}

.tasks-pagination {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    align-items: center;
}

button {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    background-color: rgb(112, 230, 112);
    color: white;
}

button:hover {
    opacity: 0.7;
}
</style>
