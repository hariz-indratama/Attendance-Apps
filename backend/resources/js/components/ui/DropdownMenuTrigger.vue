<script setup lang="ts">
import { inject } from 'vue';

const dropdown = inject<any>('dropdown');

const handleClick = () => {
    dropdown?.toggle();
};
</script>

<template>
    <div @click="handleClick" v-bind="$attrs">
        <slot />
    </div>
</template>
