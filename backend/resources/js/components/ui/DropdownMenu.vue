<script setup lang="ts">
import { ref, provide } from 'vue';

interface DropdownMenuProps {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

const props = withDefaults(defineProps<DropdownMenuProps>(), {
    open: false,
});

const isOpen = ref(props.open);

const toggle = () => {
    isOpen.value = !isOpen.value;
    props.onOpenChange?.(isOpen.value);
};

const close = () => {
    isOpen.value = false;
    props.onOpenChange?.(false);
};

provide('dropdown', { isOpen, toggle, close });
</script>

<template>
    <div class="relative">
        <slot :isOpen="isOpen" :toggle="toggle" :close="close" />
    </div>
</template>
