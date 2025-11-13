<script setup lang="ts">
import CashierInvoiceRevenueTable from '@/components/CashierInvoiceRevenueTable.vue';
import Container from '@/components/Container.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    invoiceRevenueTable: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoice Reports',
        href: route('admin.reports.invoice'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <CashierInvoiceRevenueTable :invoiceRevenueTable="invoiceRevenueTable" />
        </Container>
    </AppLayout>
</template>
