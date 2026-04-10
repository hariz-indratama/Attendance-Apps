import { reactive, readonly } from 'vue';

interface Toast {
    id: number;
    message: string;
    type: 'success' | 'error' | 'warning' | 'info';
    visible: boolean;
}

interface ToastState {
    toasts: Toast[];
    nextId: number;
}

const state = reactive<ToastState>({
    toasts: [],
    nextId: 1
});

function addToast(message: string, type: Toast['type'] = 'success', duration: number = 3000): number {
    const id = state.nextId++;
    const toast: Toast = {
        id,
        message,
        type,
        visible: true
    };
    state.toasts.push(toast);

    setTimeout(() => {
        removeToast(id);
    }, duration);

    return id;
}

function removeToast(id: number): void {
    const index = state.toasts.findIndex(t => t.id === id);
    if (index > -1) {
        state.toasts.splice(index, 1);
    }
}

function toast(message: string, duration?: number): number {
    return addToast(message, 'success', duration);
}

function success(message: string, duration?: number): number {
    return addToast(message, 'success', duration);
}

function error(message: string, duration?: number): number {
    return addToast(message, 'error', duration);
}

function warning(message: string, duration?: number): number {
    return addToast(message, 'warning', duration);
}

function info(message: string, duration?: number): number {
    return addToast(message, 'info', duration);
}

export function useToast() {
    return {
        state: readonly(state),
        toast,
        success,
        error,
        warning,
        info,
        removeToast
    };
}
