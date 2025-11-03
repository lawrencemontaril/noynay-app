<script setup lang="ts">
import { cn } from '@/lib/utils';
import { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <form v-bind="$attrs" :class="cn('bg-zinc-50', props.class)">
        <slot />
    </form>
</template>
