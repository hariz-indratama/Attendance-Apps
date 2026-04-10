<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <div
        :class="cn(
            'z-50 min-w-[180px] overflow-hidden rounded-xl border bg-white p-1 shadow-lg',
            props.class
        )"
    >
        <slot />
    </div>
</template>
