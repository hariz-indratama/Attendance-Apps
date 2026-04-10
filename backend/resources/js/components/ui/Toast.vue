<script setup lang="ts">
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import Button from './Button.vue';
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();

const getIcon = (type: string) => {
    switch (type) {
        case 'success': return CheckCircle;
        case 'error': return AlertCircle;
        case 'warning': return AlertTriangle;
        default: return Info;
    }
};

const getIconClass = (type: string) => {
    switch (type) {
        case 'success': return 'text-green-500';
        case 'error': return 'text-red-500';
        case 'warning': return 'text-yellow-500';
        default: return 'text-blue-500';
    }
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="flex items-start gap-3 p-4 bg-white rounded-xl shadow-lg border border-gray-200"
            >
                <component
                    :is="getIcon(toast.type)"
                    :class="getIconClass(toast.type)"
                    class="w-5 h-5 shrink-0 mt-0.5"
                />
                <div class="flex-1 min-w-0">
                    <p v-if="toast.title" class="font-medium text-gray-900">{{ toast.title }}</p>
                    <p class="text-sm text-gray-600">{{ toast.message }}</p>
                </div>
                <button
                    @click="removeToast(toast.id)"
                    class="shrink-0 p-1 rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <X class="w-4 h-4 text-gray-400" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
