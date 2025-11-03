<script lang="ts" setup>
import { cn } from '@/lib/utils';
import { reactiveOmit } from '@vueuse/core';
import { CalendarHeadCell, type CalendarHeadCellProps, useForwardProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';

const props = defineProps<CalendarHeadCellProps & { class?: HTMLAttributes['class']; buttonSize?: 'icon' | 'icon12' }>();

const delegatedProps = reactiveOmit(props, 'class');

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <CalendarHeadCell
        data-slot="calendar-head-cell"
        :class="cn('rounded-md text-[0.8rem] font-normal text-muted-foreground', props.buttonSize === 'icon12' ? 'w-12' : 'w-8', props.class)"
        v-bind="forwardedProps"
    >
        <slot />
    </CalendarHeadCell>
</template>
