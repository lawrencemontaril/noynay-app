<script setup lang="ts">
import { Appointment, Invoice } from '@/types';
import { Deferred } from '@inertiajs/vue3';
import CashierPendingInvoicesTable from './CashierPendingInvoicesTable.vue';
import CashierUnpaidInvoicesTable from './CashierUnpaidInvoicesTable.vue';
import Skeleton from './ui/skeleton/Skeleton.vue';

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

    <div class="grid min-h-96 grid-cols-1 gap-4 md:grid-cols-3">
        <div class="shadow-xs">
            <Deferred :data="['invoiceStatusChart']">
                <template #fallback>
                    <Skeleton class="h-full w-full" />
                </template>

                <div class="h-full w-full rounded-md border px-2 py-4">
                    <apexchart
                        :weight="invoiceStatusChart.weight"
                        :height="invoiceStatusChart.height"
                        :type="invoiceStatusChart.type"
                        :options="invoiceStatusChart.options"
                        :series="invoiceStatusChart.series"
                    ></apexchart>
                </div>
            </Deferred>
        </div>

        <div class="shadow-xs md:col-span-2">
            <Deferred :data="['invoiceRevenuePerMonthChart']">
                <template #fallback>
                    <Skeleton class="h-full w-full" />
                </template>

                <div class="h-full w-full rounded-md border px-2 py-4">
                    <apexchart
                        :weight="invoiceRevenuePerMonthChart.weight"
                        :height="invoiceRevenuePerMonthChart.height"
                        :type="invoiceRevenuePerMonthChart.type"
                        :options="invoiceRevenuePerMonthChart.options"
                        :series="invoiceRevenuePerMonthChart.series"
                    ></apexchart>
                </div>
            </Deferred>
        </div>
    </div>
</template>
