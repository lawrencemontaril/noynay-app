<script setup lang="ts">
import { LaboratoryResult } from '@/types';
import ChartCard from './ChartCard.vue';
import ChartGrid from './ChartGrid.vue';
import LaboratoryStaffPendingLaboratoryResultsTable from './LaboratoryStaffPendingLaboratoryResultsTable.vue';

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
