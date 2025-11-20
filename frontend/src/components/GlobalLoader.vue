<template>
    <div v-if="loadingState.active" class="overlay">
        <div class="spinner"></div>
    </div>
</template>

<script setup>
import { loadingState } from '../stores/loading';
</script>

<style scoped>
.overlay {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #ddd;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>