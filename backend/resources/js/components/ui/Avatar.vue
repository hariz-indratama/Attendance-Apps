<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    src?: string;
    alt?: string;
    class?: string;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
});

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-12 w-12 text-base',
};
</script>

<template>
    <div
        :class="cn(
            'relative flex shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-blue-500 to-blue-600',
            sizeClasses[props.size],
            props.class
        )"
    >
        <img
            v-if="src"
            :src="src"
            :alt="alt"
            class="aspect-square h-full w-full object-cover"
        >
        <span
            v-else
            class="flex h-full w-full items-center justify-center font-medium text-white"
        >
            <slot />
        </span>
    </div>
</template>
