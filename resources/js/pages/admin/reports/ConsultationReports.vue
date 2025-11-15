<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    consultationTypeRanking: {
        rank: number;
        label: string;
        total: number;
    }[];
    monthlyConsultationVolume: {
        month: string;
        total: number;
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consultation Reports',
        href: route('admin.reports.consultation'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container class="space-y-8">
            <!-- Consultation Type Ranking -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-4">
                        <CardTitle>Consultation Type Ranking by Frequency</CardTitle>

                        <Button
                            as="a"
                            variant="destructive"
                            :href="route('admin.reports.consultation-type-ranking.pdf')"
                            target="_blank"
                        >
                            Download PDF
                        </Button>
                    </div>
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
                                v-for="item in consultationTypeRanking"
                                :key="item.rank"
                            >
                                <TableCell>{{ item.rank }}</TableCell>
                                <TableCell>{{ item.label }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ item.total }}</TableCell>
                            </TableRow>

                            <TableEmpty
                                v-if="!consultationTypeRanking.length"
                                :colspan="4"
                            >
                                No appointment data available.
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Monthly Consultation Volume -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-4">
                        <CardTitle>Monthly Consultation Volume</CardTitle>

                        <Button
                            as="a"
                            variant="destructive"
                            :href="route('admin.reports.consultation-volume.pdf')"
                            target="_blank"
                        >
                            Download PDF
                        </Button>
                    </div>
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Month</TableHead>
                                <TableHead class="text-right">Total Consultations</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in monthlyConsultationVolume"
                                :key="row.month"
                            >
                                <TableCell>{{ row.month }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ row.total }}</TableCell>
                            </TableRow>
                            <TableEmpty
                                v-if="!monthlyConsultationVolume.length"
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
