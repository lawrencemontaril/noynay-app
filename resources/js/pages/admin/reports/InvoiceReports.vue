<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    invoiceRevenueTable: {
        month: string;
        total_revenue: number;
    }[];
}>();

const { formatCurrency } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoice Reports',
        href: route('admin.reports.invoice'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container class="space-y-8">
            <Card>
                <CardHeader class="flex items-center justify-between">
                    <CardTitle>Invoice Revenue Report (Last 12 Months)</CardTitle>

                    <Button
                        as="a"
                        variant="destructive"
                        :href="route('admin.reports.invoice-revenue.pdf')"
                        target="_blank"
                    >
                        Download PDF
                    </Button>
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Month</TableHead>
                                <TableHead class="text-right">Total Revenue (₱)</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in invoiceRevenueTable"
                                :key="row.month"
                            >
                                <TableCell>{{ row.month }}</TableCell>
                                <TableCell class="text-right font-semibold">
                                    {{ formatCurrency(row.total_revenue) }}
                                </TableCell>
                            </TableRow>

                            <TableEmpty
                                v-if="!invoiceRevenueTable.length"
                                :colspan="2"
                            >
                                No data available.
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </Container>
    </AppLayout>
</template>
