<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <span :class="cn('flex h-full w-full items-center justify-center', props.class)">
        <slot />
    </span>
</template>
