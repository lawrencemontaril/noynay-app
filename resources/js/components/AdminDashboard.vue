<script setup lang="ts">
import { Appointment } from '@/types';
import AdminPendingAppointmentsTable from './AdminPendingAppointmentsTable.vue';
import ChartCard from './ChartCard.vue';
import ChartGrid from './ChartGrid.vue';

defineProps<{
    pendingAppointments: Appointment[];
    appointmentStatusChart: any;
    appointmentServiceChart: any;
    patientsByGenderChart: any;
    patientsByAgeGroupChart: any;
    patientsByCivilStatusChart: any;
    consultationsOverTimeChart: any;
    vitalSignsByAgeGroupChart: any;
    patientBmiChart: any;
    patientTemperatureChart: any;
    bloodPressureChart: any;
    oxygenSaturationChart: any;
    invoiceStatusChart: any;
    invoiceRevenuePerMonthChart: any;
    laboratoryResultsByTypeChart: any;
}>();
</script>

<template>
    <AdminPendingAppointmentsTable :pendingAppointments="pendingAppointments" />

    <ChartGrid :columns="3">
        <ChartCard
            chartKey="appointmentStatusChart"
            :chart="appointmentStatusChart"
            :col-span="1"
        />
        <ChartCard
            chartKey="appointmentServiceChart"
            :chart="appointmentServiceChart"
            :col-span="2"
        />
    </ChartGrid>

    <ChartGrid :columns="3">
        <ChartCard
            chartKey="patientsByGenderChart"
            :chart="patientsByGenderChart"
            when-visible
        />
        <ChartCard
            chartKey="patientsByAgeGroupChart"
            :chart="patientsByAgeGroupChart"
            when-visible
        />
        <ChartCard
            chartKey="patientsByCivilStatusChart"
            :chart="patientsByCivilStatusChart"
            when-visible
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
