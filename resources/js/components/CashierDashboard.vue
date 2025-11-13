<script setup lang="ts">
import { Appointment, Invoice } from '@/types';
import CashierPendingInvoicesTable from './CashierPendingInvoicesTable.vue';
import CashierUnpaidInvoicesTable from './CashierUnpaidInvoicesTable.vue';
import ChartCard from './ChartCard.vue';
import ChartGrid from './ChartGrid.vue';

defineProps<{
    approvedAppointments: Appointment[];
    unpaidInvoices: Invoice[];
    invoiceStatusChart: any;
    invoiceRevenuePerMonthChart: any;
}>();
</script>

<template>
    <CashierPendingInvoicesTable :approvedAppointments="approvedAppointments" />

    <CashierUnpaidInvoicesTable :unpaidInvoices="unpaidInvoices" />

    <ChartGrid :columns="3">
        <ChartCard
            chartKey="invoiceStatusChart"
            :chart="invoiceStatusChart"
            :col-span="1"
        />
        <ChartCard
            chartKey="invoiceRevenuePerMonthChart"
            :chart="invoiceRevenuePerMonthChart"
            :col-span="2"
        />
    </ChartGrid>
</template>
