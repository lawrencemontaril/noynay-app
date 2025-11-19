<script setup lang="ts">
import { LaboratoryResult } from '@/types';
import LaboratoryStaffPendingLaboratoryResultsTable from './LaboratoryStaffPendingLaboratoryResultsTable.vue';
import { ChartCard, ChartGrid } from './ui/charts';

defineProps<{
    laboratoryResultsByTypeChart: any;
    pendingLaboratoryResults: LaboratoryResult[];
}>();
</script>

<template>
    <LaboratoryStaffPendingLaboratoryResultsTable :pendingLaboratoryResults="pendingLaboratoryResults" />

    <ChartGrid :columns="3">
        <ChartCard
            :chart="laboratoryResultsByTypeChart"
            chart-key="laboratoryResultsByTypeChart"
            :col-span="2"
        />
    </ChartGrid>
</template>
