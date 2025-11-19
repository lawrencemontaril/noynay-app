<script setup lang="ts">
import { ChartCard, ChartGrid } from './ui/charts';

defineProps<{
    userActiveStatusChart: any;
    userRegistrationsPerMonthChart: any;
    usersByRoleChart: any;
}>();
</script>

<template>
    <ChartGrid :columns="3">
        <ChartCard
            chartKey="userActiveStatusChart"
            :chart="userActiveStatusChart"
            :col-span="1"
        />
        <ChartCard
            chartKey="usersByRoleChart"
            :chart="usersByRoleChart"
            :col-span="2"
        />
    </ChartGrid>

    <ChartGrid>
        <ChartCard
            chartKey="userRegistrationsPerMonthChart"
            :chart="userRegistrationsPerMonthChart"
            :col-span="1"
        />
    </ChartGrid>
</template>
