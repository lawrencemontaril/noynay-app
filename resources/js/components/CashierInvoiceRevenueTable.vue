<script setup lang="ts">
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';

defineProps<{
    invoiceRevenueTable: any;
}>();

const { formatCurrency } = useFormatters();
</script>

<template>
    <div class="mb-4 rounded-lg border shadow-xs">
        <div class="flex items-center justify-between gap-4 border-b p-4">
            <h4 class="text-lg font-semibold">Monthly Revenue (Last 12 months)</h4>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Month</TableHead>
                    <TableHead>Total Revenue (₱)</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow
                    v-for="row in invoiceRevenueTable"
                    :key="row.month"
                >
                    <TableCell>{{ row.month }}</TableCell>

                    <TableCell>{{ formatCurrency(row.total_revenue) }}</TableCell>
                </TableRow>

                <TableEmpty
                    v-if="!invoiceRevenueTable.length"
                    :colspan="2"
                >
                    No invoice revenue data
                </TableEmpty>
            </TableBody>
        </Table>
    </div>
</template>
