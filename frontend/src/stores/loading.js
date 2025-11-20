import { reactive } from 'vue';

export const loadingState = reactive({
    active: false,
    buttons: {},
});

export function startLoading(key = 'global') {
    if (key === 'global') {
        loadingState.active = true;
    } else {
        loadingState.buttons[key] = true;
    }
}

export function stopLoading(key = 'global') {
    if (key === 'global') {
        loadingState.active = false;
    } else {
        loadingState.buttons[key] = false;
    }
}