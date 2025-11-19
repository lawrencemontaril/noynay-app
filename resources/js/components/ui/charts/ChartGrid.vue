<script setup lang="ts">
import { useBreakpoints } from '@vueuse/core';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        /** Tailwind grid column count (e.g. 2, 3) */
        columns?: number;
    }>(),
    {
        columns: 1,
    },
);

// Tailwind default breakpoints
const breakpoints = useBreakpoints({
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280,
    '2xl': 1536,
});

const isMd = breakpoints.greaterOrEqual('md');

const gridStyle = computed(() => {
    return isMd.value
        ? { 'grid-template-columns': `repeat(${props.columns}, minmax(0, 1fr))` }
        : { 'grid-template-columns': `repeat(1, minmax(0, 1fr))` };
});
</script>

<template>
    <div
        class="my-4 grid min-h-96 gap-4"
        :style="gridStyle"
    >
        <slot />
    </div>
</template>
