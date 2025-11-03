<script setup lang="ts">
import { LaboratoryResult } from '@/types';
import { Deferred } from '@inertiajs/vue3';
import LaboratoryStaffPendingLaboratoryResultsTable from './LaboratoryStaffPendingLaboratoryResultsTable.vue';
import Skeleton from './ui/skeleton/Skeleton.vue';

defineProps<{
    laboratoryResultsByTypeChart: any;
    pendingLaboratoryResults: LaboratoryResult[];
}>();
</script>

<template>
    <LaboratoryStaffPendingLaboratoryResultsTable :pendingLaboratoryResults="pendingLaboratoryResults" />

    <div class="my-4 grid min-h-96 grid-cols-1 gap-4 md:grid-cols-2">
        <div class="">
            <Deferred :data="['laboratoryResultsByTypeChart']">
                <template #fallback>
                    <Skeleton class="h-full w-full" />
                </template>

                <div class="h-full w-full rounded-md border px-2 py-4">
                    <apexchart
                        :weight="laboratoryResultsByTypeChart.weight"
                        :height="laboratoryResultsByTypeChart.height"
                        :type="laboratoryResultsByTypeChart.type"
                        :options="laboratoryResultsByTypeChart.options"
                        :series="laboratoryResultsByTypeChart.series"
                    ></apexchart>
                </div>
            </Deferred>
        </div>
    </div>
</template>
