<script setup lang="ts">
import { Appointment } from '@/types';
import ChartCard from './ChartCard.vue';
import ChartGrid from './ChartGrid.vue';
import DoctorPendingConsultationsTable from './DoctorPendingConsultationsTable.vue';

defineProps<{
    patientsByGenderChart: any;
    patientsByAgeGroupChart: any;
    patientsByCivilStatusChart: any;
    consultationsOverTimeChart: any;
    vitalSignsByAgeGroupChart: any;
    patientBmiChart: any;
    patientTemperatureChart: any;
    bloodPressureChart: any;
    oxygenSaturationChart: any;
    approvedAppointments: Appointment[];
}>();
</script>

<template>
    <DoctorPendingConsultationsTable :approvedAppointments="approvedAppointments" />

    <ChartGrid :columns="3">
        <ChartCard
            chartKey="patientsByGenderChart"
            :chart="patientsByGenderChart"
        />
        <ChartCard
            chartKey="patientsByAgeGroupChart"
            :chart="patientsByAgeGroupChart"
        />
        <ChartCard
            chartKey="patientsByCivilStatusChart"
            :chart="patientsByCivilStatusChart"
        />
    </ChartGrid>

    <ChartGrid :columns="2">
        <ChartCard
            chartKey="consultationsOverTimeChart"
            :chart="consultationsOverTimeChart"
            when-visible
        />
        <ChartCard
            chartKey="vitalSignsByAgeGroupChart"
            :chart="vitalSignsByAgeGroupChart"
            when-visible
        />
    </ChartGrid>

    <ChartGrid :columns="2">
        <ChartCard
            chartKey="patientBmiChart"
            :chart="patientBmiChart"
            when-visible
        />
        <ChartCard
            chartKey="patientTemperatureChart"
            :chart="patientTemperatureChart"
            when-visible
        />
    </ChartGrid>

    <ChartGrid :columns="2">
        <ChartCard
            chartKey="bloodPressureChart"
            :chart="bloodPressureChart"
            when-visible
        />
        <ChartCard
            chartKey="oxygenSaturationChart"
            :chart="oxygenSaturationChart"
            when-visible
        />
    </ChartGrid>
</template>
