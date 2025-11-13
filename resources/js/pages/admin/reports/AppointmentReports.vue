<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    appointmentTypeRanking: {
        rank: number;
        type: string;
        label: string;
        total: number;
    }[];
    monthlyAppointmentVolume: {
        month: string;
        total_appointments: number;
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointment Reports',
        href: route('admin.reports.appointment'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container class="space-y-8">
            <!-- Appointment Type Ranking Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-4">
                        <CardTitle>Appointment Type Ranking by Frequency</CardTitle>

                        <Button
                            as="a"
                            variant="destructive"
                            :href="route('admin.reports.appointment-type-ranking.pdf')"
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
                                <TableHead>Type</TableHead>
                                <TableHead>Label</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="item in appointmentTypeRanking"
                                :key="item.type"
                            >
                                <TableCell>{{ item.rank }}</TableCell>
                                <TableCell class="font-mono text-xs text-muted-foreground">{{ item.type }}</TableCell>
                                <TableCell>{{ item.label }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ item.total }}</TableCell>
                            </TableRow>

                            <TableEmpty
                                v-if="!appointmentTypeRanking.length"
                                :colspan="4"
                            >
                                No appointment data available.
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Appointment Monthly Volume -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-4">
                        <CardTitle>Monthly Appointment Volume</CardTitle>

                        <Button
                            as="a"
                            variant="destructive"
                            :href="route('admin.reports.appointment-volume.pdf')"
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
                                <TableHead class="text-right">Total Appointments</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in monthlyAppointmentVolume"
                                :key="row.month"
                            >
                                <TableCell>{{ row.month }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ row.total_appointments }}</TableCell>
                            </TableRow>
                            <TableEmpty
                                v-if="!monthlyAppointmentVolume.length"
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
