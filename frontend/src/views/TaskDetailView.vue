<template>
    <div class="task-detail-container">
        <ErrorAlertComponent v-if="alert.message || Object.keys(alert.fieldErrors).length" :message="alert.message"
            :field-errors="alert.fieldErrors" :type="alert.type" @close="clearAlert" />

        <div v-if="loading" class="loading">
            <p>Loading task...</p>
        </div>

        <div v-else-if="error" class="error-message">
            <p>{{ error }}</p>
            <button @click="$router.push('/dashboard')" class="btn-secondary">Back to Dashboard</button>
        </div>

        <div v-else-if="task" class="task-detail">
            <div class="task-header">
                <div>
                    <button @click="$router.back()" class="back-button">← Back</button>
                    <h1 class="task-title">{{ task.title }}</h1>
                </div>
                <div v-if="task.status.name && !['Completed', 'Closed'].includes(task.status.name)" class="btn-group">
                    <button @click="showAssignModal = true" class="btn-assign">
                        Reassign Task
                    </button>
                    <button @click="completeTask" class="btn-primary">
                        Mark As Complete
                    </button>
                    <button v-if="authState.user.role=='admin'" @click="closeTask" class="btn-red">
                        Close Task
                    </button>
                </div>
            </div>

            <div class="content-grid">
                <div class="task-info-card">
                    <h2>Task Details</h2>

                    <div class="info-section">
                        <label>Description</label>
                        <p class="description">{{ task.description || 'No description provided' }}</p>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <label>Status</label>
                            <span class="status-badge" :class="getStatusClass(task.status)">
                                {{ task.status?.name || 'N/A' }}
                            </span>
                        </div>

                        <div class="info-item">
                            <label>Due Date</label>
                            <p class="due-date" :class="{ 'overdue': isOverdue(task.due_date) }">
                                📅 {{ formatDate(task.due_date) }}
                            </p>
                        </div>

                        <div class="info-item">
                            <label>Project</label>
                            <p>{{ task.project?.name || 'N/A' }}</p>
                        </div>

                        <div class="info-item">
                            <label>Created By</label>
                            <div class="user-info">
                                <div class="avatar">{{ getInitials(task.creator?.name) }}</div>
                                <div>
                                    <p class="user-name">{{ task.creator?.name || 'Unknown' }}</p>
                                    <p class="user-email">{{ task.creator?.email || '' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <label>Assigned To</label>
                            <div class="user-info">
                                <div class="avatar">{{ getInitials(task.assignee?.name) }}</div>
                                <div>
                                    <p class="user-name">{{ task.assignee?.name || 'Unassigned' }}</p>
                                    <p class="user-email">{{ task.assignee?.email || '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="comments-card">
                    <h2>Comments</h2>

                    <div class="add-comment"
                        v-if="task.status.name && !['Completed', 'Closed'].includes(task.status.name)">
                        <textarea v-model="newComment" placeholder="Add a comment..." rows="3"
                            class="comment-input"></textarea>
                        <button @click="addComment" :disabled="!newComment.trim() || submittingComment"
                            class="btn-primary">
                            {{ submittingComment ? 'Posting...' : 'Post Comment' }}
                        </button>
                    </div>

                    <div class="comments-list">
                        <div v-if="loadingComments" class="loading-comments">
                            Loading comments...
                        </div>

                        <div v-else-if="comments.length === 0" class="no-comments">
                            No comments yet. Be the first to comment!
                        </div>

                        <div v-else class="comment-items">
                            <div v-for="comment in comments" :key="comment.id" class="comment-item">
                                <div class="comment-header">
                                    <div class="comment-author">
                                        <div class="avatar small">{{ getInitials(comment.user?.name) }}</div>
                                        <span class="author-name">{{ comment.user?.name || 'Unknown' }}</span>
                                    </div>
                                    <span class="comment-date">{{ formatDateTime(comment.created_at) }}</span>
                                </div>
                                <p class="comment-body">{{ comment.body }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showAssignModal" class="modal-overlay" @click="showAssignModal = false">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>Reassign Task</h2>
                    <button @click="showAssignModal = false" class="close-button">×</button>
                </div>

                <div class="modal-body">
                    <label>Select User</label>
                    <select v-model="selectedUserId" class="user-select">
                        <option value="">-- Select a user --</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }} ({{ user.email }})
                        </option>
                    </select>

                    <label>Comment (Optional)</label>
                    <textarea v-model="assignComment" placeholder="Add a note about this reassignment..." rows="3"
                        class="comment-input"></textarea>

                </div>

                <div class="modal-footer">
                    <button @click="showAssignModal = false" class="btn-secondary">Cancel</button>
                    <button @click="assignTask" :disabled="!selectedUserId || assigningTask" class="btn-primary">
                        {{ assigningTask ? 'Assigning...' : 'Assign Task' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axiosInstance from "../api/axios";
import ErrorAlertComponent from '../components/ErrorAlertComponent.vue';
import { startLoading, stopLoading } from '../stores/loading';
import { authState } from '../auth';

const route = useRoute();

const task = ref(null);
const comments = ref([]);
const users = ref([]);
const loading = ref(true);
const loadingComments = ref(true);
const error = ref(null);
const newComment = ref('');
const submittingComment = ref(false);
const showAssignModal = ref(false);
const selectedUserId = ref('');
const assignComment = ref('');
const assigningTask = ref(false);

const alert = ref({
    message: "",
    type: "error",
    fieldErrors: {}
});

const showAlert = (message, type = "error", fieldErrors = {}) => {
    alert.value = { message, type, fieldErrors };
};

const clearAlert = () => {
    alert.value = { message: "", type: "error", fieldErrors: {} };
};

const fetchTask = async () => {
    loading.value = true;
    error.value = null;
    startLoading();

    try {
        const response = await axiosInstance.get(`/api/tasks/${route.params.id}`);
        task.value = response.data;
    } catch (err) {
        showAlert("Failed to load task.", "error");
    } finally {
        loading.value = false;
        stopLoading();
    }
};

const fetchComments = async () => {
    loadingComments.value = true;

    try {
        const res = await axiosInstance.get(`/api/tasks/${route.params.id}/comments`);
        comments.value = res.data;
    } catch (error) {
        showAlert("Could not load comments.", "error");
    } finally {
        loadingComments.value = false;
    }
};

const fetchUsers = async () => {
    try {
        startLoading();
        const res = await axiosInstance.get("/api/users");
        users.value = res.data;
    } catch (error) {
        showAlert("Failed to load user list.", "error");
    } finally {
        stopLoading();
    }
};

const addComment = async () => {
    if (!newComment.value.trim()) return;

    submittingComment.value = true;

    try {
        const res = await axiosInstance.post(`/api/tasks/${route.params.id}/comments`, {
            body: newComment.value
        });

        comments.value.push(res.data);
        newComment.value = "";

        showAlert("Comment added!", "success");
    } catch (err) {
        showAlert("Failed to post comment.", "error", err.response?.data?.errors || {});
    } finally {
        submittingComment.value = false;
    }
};

const completeTask = async () => {
    try {
        startLoading();
        await axiosInstance.patch(`/api/tasks/${route.params.id}/complete`);
        showAlert("Task marked as completed!", "success");
        await fetchTask();
    } catch (err) {
        showAlert("Could not complete task.", "error", err.response?.data?.errors || {});
    } finally {
        stopLoading();
    }
};

const closeTask = async () => {
    try {
        startLoading();
        await axiosInstance.patch(`/api/tasks/${route.params.id}/close`);
        showAlert("Task was closed!", "success");
        await fetchTask();
    } catch (err) {
        showAlert("Could not close task.", "error", err.response?.data?.errors || {});
    } finally {
        stopLoading();
    }
};

const assignTask = async () => {
    if (!selectedUserId.value) return;

    assigningTask.value = true;

    try {
        await axiosInstance.post('/api/tasks/assign', {
            task_id: task.value.id,
            user_id: selectedUserId.value,
            comment: assignComment.value || null
        });

        showAssignModal.value = false;
        selectedUserId.value = '';
        assignComment.value = '';

        await fetchTask();
        await fetchComments();

        showAlert("Task reassigned successfully!", "success");
    } catch (err) {
        showAlert("Failed to assign task.", "error", err.response?.data?.errors || {});
    } finally {
        assigningTask.value = false;
    }
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return 'No due date';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatDateTime = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
};

const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    return new Date(dueDate) < new Date();
};

const getStatusClass = (status) => {
    if (!status || !status.name) return '';
    const statusName = status.name.toLowerCase();
    if (statusName.includes('complete')) return 'status-completed';
    if (statusName.includes('progress')) return 'status-progress';
    return 'status-pending';
};

onMounted(() => {
    fetchTask();
    fetchComments();
    fetchUsers();
});
</script>

<style scoped>
.task-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.loading,
.error-message {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error-message {
    color: #dc3545;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 1rem;
}

.back-button {
    background: none;
    border: none;
    color: #4CAF50;
    cursor: pointer;
    font-size: 1rem;
    padding: 0.5rem 0;
    margin-bottom: 0.5rem;
    transition: color 0.2s;
}

.back-button:hover {
    color: #45a049;
}

.task-title {
    margin: 0;
    font-size: 2rem;
    color: #333;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.task-info-card,
.comments-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.task-info-card h2,
.comments-card h2 {
    margin: 0 0 1.5rem 0;
    font-size: 1.5rem;
    color: #333;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 0.75rem;
}

.info-section {
    margin-bottom: 2rem;
}

.info-section label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.description {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.info-item label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.info-item p {
    margin: 0;
    color: #666;
}

.status-badge {
    display: inline-block;
    padding: 0.35rem 0.85rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
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

.due-date.overdue {
    color: #dc3545;
    font-weight: 600;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #4CAF50;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.avatar.small {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
}

.user-name {
    margin: 0;
    font-weight: 500;
    color: #333;
}

.user-email {
    margin: 0;
    font-size: 0.85rem;
    color: #999;
}

.add-comment {
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.comment-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: inherit;
    font-size: 0.95rem;
    resize: vertical;
    transition: border-color 0.2s;
}

.comment-input:focus {
    outline: none;
    border-color: #4CAF50;
}

.comments-list {
    max-height: 500px;
    overflow-y: auto;
}

.loading-comments,
.no-comments {
    text-align: center;
    padding: 2rem;
    color: #999;
}

.comment-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.comment-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #4CAF50;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.author-name {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.comment-date {
    font-size: 0.85rem;
    color: #999;
}

.comment-body {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.btn-primary,
.btn-secondary,
.btn-red,
.btn-assign {
    padding: 0.65rem 1.25rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #45a049;
}

.btn-primary:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.btn-red {
    background: #f30020;
    color: white;
}

.btn-red:hover:not(:disabled) {
    background: #f83f58;
}

.btn-assign {
    background: #3775fa;
    color: white;
}

.btn-assign:hover:not(:disabled) {
    background: #6494fa;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-content textarea,
.modal-content select[multiple] {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  box-sizing: border-box;
  min-height: 120px;
  resize: vertical;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.close-button {
    background: none;
    border: none;
    font-size: 2rem;
    color: #999;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-button:hover {
    color: #333;
}

.modal-body {
    padding: 1.5rem;
}

.modal-body label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.5rem;
    margin-top: 1rem;
}

.modal-body label:first-child {
    margin-top: 0;
}

.user-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}

.user-select:focus {
    outline: none;
    border-color: #4CAF50;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-group {
    display: flex;
    gap: 1em;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .task-header {
        flex-direction: column;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>