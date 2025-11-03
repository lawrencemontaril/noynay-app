<script lang="ts" setup>
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { LabelProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { useFormField } from './useFormField';

const props = defineProps<LabelProps & { class?: HTMLAttributes['class']; required?: boolean }>();

const { error, formItemId } = useFormField();
</script>

<template>
    <Label
        data-slot="form-label"
        :data-error="!!error"
        :class="cn('text-xs font-semibold uppercase data-[error=true]:text-destructive', props.class)"
        :for="formItemId"
    >
        <slot />
        <span
            v-if="required"
            class="-ml-1.5 font-semibold text-destructive"
        >
            *
        </span>
    </Label>
</template>
