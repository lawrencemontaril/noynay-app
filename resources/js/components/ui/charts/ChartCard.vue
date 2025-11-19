<script setup lang="ts">
import { Deferred, WhenVisible } from '@inertiajs/vue3';
import Skeleton from './ui/skeleton/Skeleton.vue';

withDefaults(
    defineProps<{
        chart: any;
        chartKey: string;
        colSpan?: number;
        whenVisible?: boolean;
    }>(),
    {
        colSpan: 1,
        whenVisible: false,
    },
);
</script>

<template>
    <div
        class="rounded-md border shadow-xs"
        :style="{ 'grid-column': `span ${colSpan} / span ${colSpan}` }"
    >
        <component
            :is="whenVisible ? WhenVisible : Deferred"
            :data="[chartKey]"
        >
            <template #fallback>
                <Skeleton class="h-full w-full" />
            </template>

            <div class="h-full w-full px-2 py-4">
                <apexchart
                    :weight="chart.weight"
                    :height="chart.height"
                    :type="chart.type"
                    :options="chart.options"
                    :series="chart.series"
                />
            </div>
        </component>
    </div>
</template>
