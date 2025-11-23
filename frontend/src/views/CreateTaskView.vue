<template>
    <div class="create-task-page">
        <ErrorAlertComponent v-if="alertMessage || Object.keys(alertFieldErrors).length" :message="alertMessage"
            :type="alertType" :fieldErrors="alertFieldErrors" @close="clearAlert" />

        <h2>Create New Task</h2>
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

            <button type="submit" :disabled="loading">
                Create Task
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
        form.value.status_id= 1 //in progress status
        form.value.created_by_id= authState.user.id; //user creating the request

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
            status_id: "",
            created_by_id: "",
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
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
}

.task-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group label {
    font-weight: 600;
}

input,
textarea,
select {
    width: 100%;
    padding: 8px;
}

button {
    background: #2563eb;
    color: white;
    padding: 8px 12px;
    border: none;
    cursor: pointer;
}

button:disabled {
    background: gray;
}
</style>
