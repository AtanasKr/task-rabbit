<template>
  <div class="projects-page">
    <header class="top-bar">
      <div>
        <h1>Projects</h1>
      </div>
      <div class="controls">
        <div class="search-box">
          <input v-model="searchQuery" type="text" placeholder="Search projects..." @input="loadProjectsDebounced"
            class="search-input" />
          <span class="search-icon">🔍</span>
        </div>
        <button @click="openCreateForm">Create Project</button>
      </div>
    </header>

    <main>
      <div v-if="loading">Loading projects...</div>
      <table v-else class="project-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Start</th>
            <th>End</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="project in projects.data" :key="project.id">
            <td>{{ project.name }}</td>
            <td>{{ project.start_date }}</td>
            <td>{{ project.end_date }}</td>
            <td>{{ project.description }}</td>
            <td>
              <button class="edit" @click="openEditForm(project)">Edit</button>
              <button class="delete" @click="deleteProject(project.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="projects.meta" class="pagination">
        <button :disabled="projects.meta.current_page === 1" @click="changePage(projects.meta.current_page - 1)">
          Prev
        </button>

        <span>Page {{ projects.meta.current_page }} of {{ projects.meta.last_page }}</span>

        <button :disabled="projects.meta.current_page === projects.meta.last_page"
          @click="changePage(projects.meta.current_page + 1)">
          Next
        </button>
      </div>
    </main>

    <div v-if="showForm" class="modal">
      <div class="modal-content">
        <h2>{{ editMode ? "Edit Project" : "Create Project" }}</h2>
        <ErrorAlertComponent :message="errorMessage" :field-errors="errors" @close="clearErrors" />

        <form @submit.prevent="saveProject">
          <label>
            Name:
            <input v-model="form.name" type="text" required />
          </label>

          <label>
            Description:
            <textarea v-model="form.description"></textarea>
          </label>

          <label>
            Start Date:
            <input v-model="form.start_date" type="date" required />
          </label>

          <label>
            End Date:
            <input v-model="form.end_date" type="date" required />
          </label>

          <div v-if="editMode && currentMembers.length">
            <h3>Current Members:</h3>
            <ul>
              <li v-for="member in currentMembers" :key="member.id">
                {{ member.name }} ({{ member.email }})
                <button type="button" class="delete" @click="removeMember(member.id)">Remove</button>
              </li>
            </ul>
          </div>

          <label>
            {{ editMode ? 'Add New Members:' : 'Members:' }}<br>
            <select v-model="newMemberIds" multiple>
              <option v-for="user in availableUsers()" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
          </label>

          <div v-if="newMemberIds.length">
            <h3>Members to Add:</h3>
            <ul>
              <li v-for="userId in newMemberIds" :key="userId">
                {{users.find(u => u.id === userId)?.name}}
              </li>
            </ul>
          </div>

          <div class="form-actions">
            <button type="submit">{{ editMode ? "Update" : "Create" }}</button>
            <button class="delete" type="button" @click="closeForm">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axiosInstance from "../api/axios";
import { startLoading, stopLoading } from '../stores/loading';
import { useErrorHandler } from '../composables/userErrorHandler';
import ErrorAlertComponent from "../components/ErrorAlertComponent.vue";

const projects = ref({ data: [], meta: null });
const loading = ref(true);
const showForm = ref(false);
const editMode = ref(false);
const users = ref([]);
const currentMembers = ref([]);
const newMemberIds = ref([]);

const { errors, errorMessage, handleError, clearErrors } = useErrorHandler();

const form = ref({
  id: null,
  name: "",
  description: "",
  start_date: "",
  end_date: "",
});

const searchQuery = ref("");
const currentPage = ref(1);
const perPage = 10;
let debounceTimer = null;

const loadProjects = async () => {
  loading.value = true;
  startLoading();
  try {
    const res = await axiosInstance.get("/api/projects", {
      params: {
        page: currentPage.value,
        per_page: perPage,
        search: searchQuery.value || undefined,
        paginate: true,
      }
    });

    projects.value = {
      data: res.data.data,
      meta: {
        current_page: res.data.current_page,
        last_page: res.data.last_page,
        per_page: res.data.per_page,
        total: res.data.total
      }
    };
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
    stopLoading();
  }
};

const loadProjectsDebounced = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    loadProjects();
  }, 500);
};

const loadUsers = async () => {
  try {
    const res = await axiosInstance.get("/api/users");
    users.value = res.data;
  } catch (error) {
    handleError(error);
  }
};

const changePage = (page) => {
  currentPage.value = page;
  loadProjects();
};

watch(searchQuery, () => {
  currentPage.value = 1;
  loadProjectsDebounced();
});

onMounted(() => {
  loadProjects();
  loadUsers();
});

const openCreateForm = () => {
  editMode.value = false;
  clearErrors();
  form.value = {
    id: null,
    name: "",
    description: "",
    start_date: "",
    end_date: "",
  };
  currentMembers.value = [];
  newMemberIds.value = [];
  showForm.value = true;
};

const openEditForm = (project) => {
  editMode.value = true;
  clearErrors();
  form.value = { ...project };
  currentMembers.value = project.members || [];
  newMemberIds.value = [];
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  clearErrors();
  currentMembers.value = [];
  newMemberIds.value = [];
};

const saveProject = async () => {
  try {
    startLoading();
    clearErrors();

    let projectId;

    if (editMode.value) {
      await axiosInstance.put(`/api/projects/${form.value.id}`, form.value);
      projectId = form.value.id;
    } else {
      const res = await axiosInstance.post("/api/projects", form.value);
      projectId = res.data.id;
    }

    const memberIdsToAdd = editMode.value ? newMemberIds.value : newMemberIds.value;

    if (memberIdsToAdd.length > 0) {
      await axiosInstance.post(`/api/projects/${projectId}/members`, { user_ids: memberIdsToAdd });
    }

    await loadProjects();
    closeForm();
  } catch (error) {
    handleError(error);
  } finally {
    stopLoading();
  }
};

const deleteProject = async (id) => {
  if (!confirm("Are you sure?")) return;
  try {
    startLoading();
    await axiosInstance.delete(`/api/projects/${id}`);
    await loadProjects();
  } catch (error) {
    handleError(error);
  } finally {
    stopLoading();
  }
};

const removeMember = async (userId) => {
  if (!form.value.id) return;

  if (!confirm("Are you sure you want to remove this member?")) return;

  try {
    startLoading();

    await axiosInstance.delete(`/api/projects/${form.value.id}/members`, {
      data: { user_ids: [userId] }
    });

    currentMembers.value = currentMembers.value.filter(member => member.id !== userId);

  } catch (error) {
    handleError(error);
  } finally {
    stopLoading();
  }
};

const availableUsers = () => {
  const currentMemberIds = currentMembers.value.map(m => m.id);
  return users.value.filter(user => !currentMemberIds.includes(user.id));
};
</script>

<style scoped>
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-left: 10rem;
  padding-right: 10rem;
}

.controls {
  display: flex;
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

.project-table {
  width: 100%;
  border-collapse: collapse;
}

.project-table th,
.project-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #ddd;
  text-align: center;
}

.edit {
  background: #3498db;
  color: white;
}

.delete {
  background: #e74c3c;
  color: white;
  margin-left: 0.5rem;
}

.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  gap: 1rem;
  align-items: center;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  width: 450px;
  max-width: 90%;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  gap: 18px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.modal-content h2 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 1.6rem;
  color: #111827;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.modal-content label {
  display: flex;
  flex-direction: column;
  font-weight: 600;
  color: #374151;
}

.modal-content input,
.modal-content textarea,
.modal-content select {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
  margin-top: 6px;
}

.modal-content input:focus,
.modal-content textarea:focus,
.modal-content select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

textarea {
  resize: vertical;
  min-height: 80px;
}

.modal-content ul {
  padding-left: 1rem;
  margin-top: 6px;
}

.modal-content ul li {
  margin-bottom: 4px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-content ul li button.delete {
  background: #e74c3c;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.8rem;
  border: none;
  cursor: pointer;
}

.form-actions {
  margin-top: 1rem;
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.form-actions button {
  flex: 1;
  padding: 10px 0;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.1s;
}

.form-actions button[type="submit"] {
  background: #2563eb;
  color: white;
}

.form-actions button[type="submit"]:hover:not(:disabled) {
  background-color: #1d4ed8;
  transform: translateY(-1px);
}

.form-actions button.delete {
  background: #9ca3af;
  color: white;
}

.form-actions button.delete:hover {
  background: #7b8794;
}

select[multiple] {
  min-height: 80px;
}

h3 {
  margin: 10px 0 6px;
  font-weight: 600;
  color: #111827;
}

.project-search {
  padding: 0.5em;
  border-radius: 3px;
  border: none;
}

.members-group {
  display: flex;
  flex-direction: column;
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
</style>