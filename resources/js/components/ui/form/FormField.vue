<script setup lang="ts">
import { cn } from '@/lib/utils';
import { HTMLAttributes } from 'vue';

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <div :class="cn('flex flex-col gap-2', props.class)">
        <slot />
    </div>
</template>
