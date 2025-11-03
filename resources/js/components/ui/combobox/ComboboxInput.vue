<script setup lang="ts">
import { cn } from '@/lib/utils';
import { reactiveOmit } from '@vueuse/core';
import { ComboboxInput, type ComboboxInputEmits, type ComboboxInputProps, useForwardPropsEmits } from 'reka-ui';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<
    ComboboxInputProps & {
        class?: HTMLAttributes['class'];
    }
>();

const emits = defineEmits<ComboboxInputEmits>();

const delegatedProps = reactiveOmit(props, 'class');

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <div
        data-slot="command-input-wrapper"
        :class="cn('relative flex h-9 items-center gap-2 rounded-md border border-input bg-background shadow-xs', props.class)"
    >
        <ComboboxInput
            data-slot="command-input"
            class="disabled:opacity-50', flex h-10 w-full rounded-md bg-transparent px-4 py-3 text-sm outline-hidden placeholder:text-muted-foreground disabled:cursor-not-allowed"
            v-bind="{ ...forwarded, ...$attrs }"
        >
            <slot />
        </ComboboxInput>
    </div>
</template>
