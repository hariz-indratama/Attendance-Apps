<script setup lang="ts">
import { DialogRoot, DialogTrigger, DialogPortal, DialogOverlay, DialogContent, DialogTitle, DialogDescription, DialogClose } from 'radix-vue';
import { cn } from '@/lib/utils';

interface DialogProps {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

const props = withDefaults(defineProps<DialogProps>(), {
    open: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const handleOpenChange = (open: boolean) => {
    emit('update:open', open);
    props.onOpenChange?.(open);
};
</script>

<template>
    <DialogRoot :open="open" @update:open="handleOpenChange">
        <slot />
    </DialogRoot>
</template>
