<template>
  <div class="users-page">
    <header class="top-bar">
      <h1>Users</h1>

      <div class="controls">
        <input class="user-search" type="text" v-model="searchQuery" placeholder="Search users..." />
      </div>
    </header>

    <main>
      <div v-if="loading">Loading users...</div>

      <table v-else class="users-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="user in users.data" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.role }}</td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <button class="delete" @click="deleteUser(user.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="users.meta" class="pagination">
        <button :disabled="users.meta.current_page === 1" @click="changePage(users.meta.current_page - 1)">
          Prev
        </button>

        <span>Page {{ users.meta.current_page }} of {{ users.meta.last_page }}</span>

        <button :disabled="users.meta.current_page === users.meta.last_page"
          @click="changePage(users.meta.current_page + 1)">
          Next
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axiosInstance from '../api/axios';
import { startLoading, stopLoading } from '../stores/loading';
import { useErrorHandler } from '../composables/userErrorHandler';

const users = ref({ data: [], meta: null });
const loading = ref(true);

const searchQuery = ref("");
const currentPage = ref(1);
const perPage = 10;

let debounceTimer = null;

const { errors, errorMessage, handleError, clearErrors } = useErrorHandler();

// Fetch users
const loadUsers = async () => {
  loading.value = true;
  startLoading();

  try {
    const res = await axiosInstance.get("/api/users", {
      params: {
        page: currentPage.value,
        per_page: perPage,
        search: searchQuery.value || undefined,
        paginate: true,
      }
    });

    users.value = {
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

const loadUsersDebounced = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => loadUsers(), 400);
};

const changePage = (page) => {
  currentPage.value = page;
  loadUsers();
};

watch(searchQuery, () => {
  currentPage.value = 1;
  loadUsersDebounced();
});

onMounted(() => {
  loadUsers();
});

// Delete user
const deleteUser = async (id) => {
  if (!confirm("Are you sure?")) return;

  try {
    startLoading();
    await axiosInstance.delete(`/api/users/${id}`);
    await loadUsers();
  } catch (error) {
    handleError(error);
  } finally {
    stopLoading();
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString();
};
</script>

<style scoped>
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

button {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  background-color: rgb(112, 230, 112);
  color: white;
}

.controls {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.user-search {
  padding: 0.5em;
  border-radius: 3px;
  border: none;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #ddd;
  text-align: center;
}

.delete {
  background: #e74c3c;
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
}

.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  gap: 1rem;
  align-items: center;
}
</style>