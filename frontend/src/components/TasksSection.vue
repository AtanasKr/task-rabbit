<template>
    <div class="tasks-card">
        <div class="card-header">
            <h2>Your Assigned Tasks</h2>

            <div class="search-box">
                <input v-model="searchQuery" type="text" placeholder="Search tasks..." @input="debouncedSearch"
                    class="search-input" />
                <span class="search-icon">🔍</span>
            </div>
        </div>

        <div v-if="loading" class="loading">
            <p>Loading tasks...</p>
        </div>

        <div v-else-if="error" class="error">
            <p>{{ error }}</p>
        </div>

        <div v-else-if="assignedTasks.length === 0" class="no-tasks">
            <p>No assigned tasks</p>
        </div>

        <div v-else class="tasks-list">
            <div v-for="task in paginatedAssigned" :key="task.id" class="task-item"
                :class="{ 'task-overdue': isOverdue(task) }" @click="goToTask(task.id)">
                <div class="task-content">
                    <h3 class="task-title">{{ task.title }}</h3>
                    <p class="task-description">
                        {{ task.description || "No description provided" }}
                    </p>

                    <div class="task-meta">
                        <span class="task-date">📅 Due: {{ formatDate(task.due_date) }}</span>
                        <span class="task-status" :class="getStatusClass(task.status)">
                            {{ task.status.name }}
                        </span>
                    </div>
                </div>
                <div class="task-arrow">→</div>
            </div>
        </div>

        <div v-if="assignedTasks.length > perPage" class="pagination">
            <button @click="changeAssignedPage(currentAssignedPage - 1)" :disabled="currentAssignedPage === 1"
                class="pagination-btn">
                Previous
            </button>

            <span class="pagination-info">
                Page {{ currentAssignedPage }} of {{ totalAssignedPages }}
            </span>

            <button @click="changeAssignedPage(currentAssignedPage + 1)"
                :disabled="currentAssignedPage === totalAssignedPages" class="pagination-btn">
                Next
            </button>
        </div>
    </div>

    <div class="tasks-card completed-card">
        <div class="card-header">
            <h2>Completed Tasks</h2>

            <div class="search-box">
                <input v-model="completedSearchQuery" type="text" placeholder="Search completed tasks..."
                    @input="debouncedCompletedSearch" class="search-input" />
                <span class="search-icon">🔍</span>
            </div>
        </div>

        <div v-if="completedTasks.length === 0" class="no-tasks">
            <p>No completed tasks</p>
        </div>

        <div v-else class="tasks-list">
            <div v-for="task in paginatedCompleted" :key="task.id" class="task-item completed-item"
                @click="goToTask(task.id)">
                <div class="task-content">
                    <h3 class="task-title">{{ task.title }}</h3>
                    <p class="task-description">
                        {{ task.description || "No description provided" }}
                    </p>

                    <div class="task-meta">
                        <span class="task-date">
                            ✔ Completed: {{ formatDate(task.updated_at) }}
                        </span>
                        <span class="task-status status-completed">Completed</span>
                    </div>
                </div>
                <div class="task-arrow">→</div>
            </div>
        </div>

        <div v-if="completedTasks.length > perPage" class="pagination">
            <button @click="changeCompletedPage(currentCompletedPage - 1)" :disabled="currentCompletedPage === 1"
                class="pagination-btn">
                Previous
            </button>

            <span class="pagination-info">
                Page {{ currentCompletedPage }} of {{ totalCompletedPages }}
            </span>

            <button @click="changeCompletedPage(currentCompletedPage + 1)"
                :disabled="currentCompletedPage === totalCompletedPages" class="pagination-btn">
                Next
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axiosInstance from "../api/axios";

const router = useRouter();

const tasks = ref([]);
const loading = ref(false);
const error = ref(null);

const searchQuery = ref("");
const completedSearchQuery = ref("");

const perPage = 3;

const currentAssignedPage = ref(1);
const assignedTasks = computed(() =>
    tasks.value
        .filter(task => task.status?.name !== "Completed" && task.status?.name !== "Closed")
        .filter(task => task.title.toLowerCase().includes(searchQuery.value.toLowerCase()))
);
const totalAssignedPages = computed(() =>
    Math.ceil(assignedTasks.value.length / perPage)
);
const paginatedAssigned = computed(() =>
    assignedTasks.value.slice(
        (currentAssignedPage.value - 1) * perPage,
        currentAssignedPage.value * perPage
    )
);

const currentCompletedPage = ref(1);
const completedTasks = computed(() =>
    tasks.value
        .filter(task => task.status?.name === "Completed")
        .filter(task => task.title.toLowerCase().includes(completedSearchQuery.value.toLowerCase()))
);
const totalCompletedPages = computed(() =>
    Math.ceil(completedTasks.value.length / perPage)
);
const paginatedCompleted = computed(() =>
    completedTasks.value.slice(
        (currentCompletedPage.value - 1) * perPage,
        currentCompletedPage.value * perPage
    )
);

const fetchTasks = async () => {
    loading.value = true;
    try {
        const response = await axiosInstance.get("/api/tasks", {
            params: {
                assigned_only: true,
                paginate: false
            },
        });
        tasks.value = response.data.data || response.data;
    } catch (err) {
        console.error(err);
        error.value = "Failed to load tasks.";
    } finally {
        loading.value = false;
    }
};

let debounceTimerAssigned, debounceTimerCompleted;
const debouncedSearch = () => {
    clearTimeout(debounceTimerAssigned);
    debounceTimerAssigned = setTimeout(() => {
        currentAssignedPage.value = 1;
    }, 300);
};
const debouncedCompletedSearch = () => {
    clearTimeout(debounceTimerCompleted);
    debounceTimerCompleted = setTimeout(() => {
        currentCompletedPage.value = 1;
    }, 300);
};

const changeAssignedPage = (page) => {
    if (page >= 1 && page <= totalAssignedPages.value) currentAssignedPage.value = page;
};
const changeCompletedPage = (page) => {
    if (page >= 1 && page <= totalCompletedPages.value) currentCompletedPage.value = page;
};

const goToTask = (id) => router.push(`/tasks/${id}`);
const isOverdue = (task) => task.due_date && task.status?.name !== "Completed" && new Date(task.due_date) < new Date();
const formatDate = (date) => date ? new Date(date).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) : "No date";
const getStatusClass = (status) => {
    if (!status) return "";
    const s = status.name.toLowerCase();
    if (s.includes("progress")) return "status-progress";
    if (s.includes("completed")) return "status-completed";
    return "status-pending";
};

onMounted(() => fetchTasks());
</script>

<style scoped>
.tasks-card {
    background: rgba(84, 215, 248, 0.322);
    padding: 2rem;
    border-radius: 8px;
    max-width: 70rem;
    margin: 2rem auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.completed-card {
    background: #e0ffe4;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.search-box {
    position: relative;
    width: 300px;
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

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.task-item {
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: 0.2s;
}

.task-item:hover {
    border-color: #4caf50;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.15);
    transform: translateX(4px);
}

.task-overdue {
    border: 2px solid red !important;
}

.completed-item {
    opacity: 0.7;
}

.completed-item:hover {
    opacity: 1;
}

.task-title {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.task-description {
    margin-bottom: 0.75rem;
    color: #666;
}

.task-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.task-status {
    padding: 0.3rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-progress {
    background: #fff3cd;
    color: #856404;
}

.status-pending {
    background: #f8d7da;
    color: #721c24;
}

.task-arrow {
    font-size: 1.5rem;
    color: #4caf50;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2rem;
}

.pagination-btn {
    padding: 0.5rem 1.25rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 6px;
    cursor: pointer;
}

.pagination-btn:hover:not(:disabled) {
    background: #4caf50;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.5;
}
</style>