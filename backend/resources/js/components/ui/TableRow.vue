<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <tr :class="cn('border-b border-gray-200 transition-colors hover:bg-gray-50/50 data-[state=selected]:bg-gray-50', props.class)">
        <slot />
    </tr>
</template>
