<template>
    <div class="create-task-page">
        <h2>Create New Task</h2>

        <form @submit.prevent="createTask" class="task-form">
            <!-- Title -->
            <div class="form-group">
                <label>Title *</label>
                <input v-model="form.title" type="text" required placeholder="Enter task title" />
            </div>

            <!-- Description -->
            <div class="form-group">
                <label>Description</label>
                <textarea v-model="form.description" placeholder="Enter task description"></textarea>
            </div>

            <!-- Project -->
            <div class="form-group">
                <label>Project *</label>
                <select v-model="form.project_id" required>
                    <option value="">-- Select a Project --</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.name }}
                    </option>
                </select>
            </div>

            <!-- Due Date -->
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" v-model="form.due_date" />
            </div>

            <!-- Errors -->
            <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

            <!-- Submit -->
            <button type="submit" :disabled="loading">
                {{ loading ? "Creating..." : "Create Task" }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const form = ref({
    title: "",
    description: "",
    project_id: "",
    due_date: "",
});

const projects = ref([]);
const errorMessage = ref("");
const loading = ref(false);

// Fetch projects from backend
const getProjects = async () => {
    try {
        const res = await axios.get("/projects");
        projects.value = res.data;
    } catch (err) {
        console.error(err);
    }
};

// Submit form
const createTask = async () => {
    try {
        loading.value = true;
        errorMessage.value = "";

        const res = await axios.post("/tasks", form.value);

        alert("Task created successfully!");

        // Reset form
        form.value = {
            title: "",
            description: "",
            project_id: "",
            due_date: "",
        };

    } catch (error) {
        console.log(error);
        errorMessage.value = error.response?.data?.message || "Error creating task.";
    } finally {
        loading.value = false;
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

.error-msg {
    color: red;
    font-weight: 600;
}
</style>
