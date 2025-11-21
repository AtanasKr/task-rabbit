<template>
    <transition name="fade">
        <div v-if="message || hasFieldErrors" :class="['alert', `alert-${type}`]">
            <div class="alert-content">
                <span class="alert-icon">{{ icon }}</span>
                <div class="alert-message">
                    <p v-if="message" class="alert-text">{{ message }}</p>

                    <ul v-if="hasFieldErrors" class="error-list">
                        <li v-for="(error, field) in fieldErrors" :key="field">
                            <strong>{{ formatFieldName(field) }}:</strong> {{ formatError(error) }}
                        </li>
                    </ul>
                </div>
                <button v-if="dismissible" @click="$emit('close')" class="alert-close">
                    ×
                </button>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: {
        type: String,
        default: '',
    },
    fieldErrors: {
        type: Object,
        default: () => ({}),
    },
    type: {
        type: String,
        default: 'error',
        validator: (value) => ['error', 'warning', 'info', 'success'].includes(value),
    },
    dismissible: {
        type: Boolean,
        default: true,
    },
});

defineEmits(['close']);

const hasFieldErrors = computed(() => {
    return Object.keys(props.fieldErrors).length > 0;
});

const icon = computed(() => {
    const icons = {
        error: '⚠',
        warning: '⚠',
        info: 'ℹ',
        success: '✓',
    };
    return icons[props.type];
});

const formatFieldName = (field) => {
    return field
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const formatError = (error) => {
    return Array.isArray(error) ? error[0] : error;
};
</script>

<style scoped>
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    border-left: 4px solid;
}

.alert-error {
    background-color: #fee;
    border-color: #e74c3c;
    color: #c0392b;
}

.alert-warning {
    background-color: #fef5e7;
    border-color: #f39c12;
    color: #d68910;
}

.alert-info {
    background-color: #ebf5fb;
    border-color: #3498db;
    color: #2874a6;
}

.alert-success {
    background-color: #eafaf1;
    border-color: #27ae60;
    color: #1e8449;
}

.alert-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert-icon {
    font-size: 1.25rem;
    font-weight: bold;
    flex-shrink: 0;
}

.alert-message {
    flex: 1;
}

.alert-text {
    margin: 0;
    font-weight: 500;
}

.error-list {
    margin: 0.5rem 0 0 0;
    padding-left: 1.25rem;
    list-style: disc;
}

.error-list li {
    margin: 0.25rem 0;
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: inherit;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.alert-close:hover {
    opacity: 1;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>