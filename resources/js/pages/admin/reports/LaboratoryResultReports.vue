<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    laboratoryResultTypeRanking: {
        rank: number;
        label: string;
        total: number;
    }[];
    monthlyLaboratoryResultVolume: {
        month: string;
        total: number;
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laboratory Reports',
        href: route('admin.reports.laboratory-result'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container class="space-y-8">
            <!-- Laboratory Type Ranking -->
            <Card>
                <CardHeader class="flex items-center justify-between gap-4">
                    <CardTitle>Laboratory Type Ranking by Frequency</CardTitle>

                    <Button
                        as="a"
                        variant="destructive"
                        :href="route('admin.reports.laboratory-result-type-ranking.pdf')"
                        target="_blank"
                    >
                        Download PDF
                    </Button>
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-20">Rank</TableHead>
                                <TableHead>Label</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="item in laboratoryResultTypeRanking"
                                :key="item.rank"
                            >
                                <TableCell>{{ item.rank }}</TableCell>
                                <TableCell>{{ item.label }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ item.total }}</TableCell>
                            </TableRow>

                            <TableEmpty
                                v-if="!laboratoryResultTypeRanking.length"
                                :colspan="4"
                            >
                                No laboratory data available.
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Monthly Laboratory Volume -->
            <Card>
                <CardHeader class="flex items-center justify-between gap-4">
                    <CardTitle>Monthly Laboratory Volume</CardTitle>

                    <Button
                        as="a"
                        variant="destructive"
                        :href="route('admin.reports.laboratory-result-volume.pdf')"
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
                                <TableHead class="text-right">Total Laboratories</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in monthlyLaboratoryResultVolume"
                                :key="row.month"
                            >
                                <TableCell>{{ row.month }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ row.total }}</TableCell>
                            </TableRow>
                            <TableEmpty
                                v-if="!monthlyLaboratoryResultVolume.length"
                                :colspan="2"
                            >
                                <TableCell class="py-4 text-center text-muted-foreground">No data available</TableCell>
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </Container>
    </AppLayout>
</template>
