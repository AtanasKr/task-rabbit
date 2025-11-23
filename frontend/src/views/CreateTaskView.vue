<template>
    <div class="create-task-page">
        <ErrorAlertComponent v-if="alertMessage || Object.keys(alertFieldErrors).length" :message="alertMessage"
            :type="alertType" :fieldErrors="alertFieldErrors" @close="clearAlert" />

        <h2 class="page-title">Create New Task</h2>
        <form @submit.prevent="createTask" class="task-form">
            <div class="form-group">
                <label>Title *</label>
                <input v-model="form.title" type="text" required placeholder="Enter task title" />
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea v-model="form.description" placeholder="Enter task description"></textarea>
            </div>

            <div class="form-group">
                <label>Project *</label>
                <select v-model="form.project_id" required @change="loadAssignees">
                    <option value="">-- Select a Project --</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.name }}
                    </option>
                </select>
            </div>

            <div class="form-group" v-if="assignees.length > 0">
                <label>Assign To *</label>
                <select v-model="form.assigned_to_id" required>
                    <option value="">-- Select User --</option>
                    <option v-for="user in assignees" :key="user.id" :value="user.id">
                        {{ user.name }} ({{ user.email }})
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>Due Date</label>
                <input type="date" v-model="form.due_date" />
            </div>

            <button type="submit" :disabled="loading" class="submit-btn">
                {{ loading ? 'Creating...' : 'Create Task' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axiosInstance from "../api/axios";
import ErrorAlertComponent from "../components/ErrorAlertComponent.vue";
import { startLoading, stopLoading } from '../stores/loading';
import { authState } from '../auth';

const form = ref({
    title: "",
    description: "",
    project_id: "",
    assigned_to_id: "",
    due_date: "",
});

const projects = ref([]);
const assignees = ref([]);

const alertMessage = ref("");
const alertType = ref("error");
const alertFieldErrors = ref({});
const loading = ref(false);

const clearAlert = () => {
    alertMessage.value = "";
    alertFieldErrors.value = {};
};

const getProjects = async () => {
    startLoading();
    try {
        const res = await axiosInstance.get("/api/projects");
        projects.value = res.data;
    } finally {
        stopLoading();
    }
};

const loadAssignees = async () => {
    if (!form.value.project_id) {
        assignees.value = [];
        return;
    }
    startLoading();
    try {
        const res = await axiosInstance.get(`/api/projects/${form.value.project_id}`);
        assignees.value = res.data.members || [];
    } finally {
        stopLoading();
    }
};

const createTask = async () => {
    clearAlert();
    try {
        startLoading();

        await axiosInstance.post("/api/tasks", form.value);

        alertType.value = "success";
        alertMessage.value = "Task created successfully!";
        alertFieldErrors.value = {};

        form.value = {
            title: "",
            description: "",
            project_id: "",
            assigned_to_id: "",
            due_date: "",
        };
        assignees.value = [];
    } catch (error) {
        alertType.value = "error";

        if (error.response?.status === 422) {
            alertFieldErrors.value = error.response.data.errors || {};
        } else {
            alertMessage.value = error.response?.data?.message || "Error creating task.";
        }
    } finally {
        stopLoading();
    }
};

onMounted(() => {
    getProjects();
});
</script>

<style scoped>
.create-task-page {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    background-color: #f9fafb;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.page-title {
    text-align: center;
    margin-bottom: 25px;
    font-size: 1.8rem;
    color: #111827;
}

.task-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
    align-items: center;
}

.form-group {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

input,
textarea,
select {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}

input:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

textarea {
    resize: vertical;
    min-height: 80px;
}

.submit-btn {
    width: 100%;
    background: #2563eb;
    color: white;
    padding: 12px 0;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
}

.submit-btn:hover:not(:disabled) {
    background-color: #1d4ed8;
    transform: translateY(-1px);
}

.submit-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}
</style>
