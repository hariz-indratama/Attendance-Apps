<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});
</script>

<template>
    <div
        :class="cn(
            'relative flex cursor-pointer select-none items-center gap-2 rounded-lg px-3 py-2 text-sm outline-none transition-colors hover:bg-gray-100 hover:text-gray-900',
            props.disabled ? 'pointer-events-none opacity-50' : '',
            props.class
        )"
    >
        <slot />
    </div>
</template>
