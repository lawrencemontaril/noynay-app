<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    mostLoyalPatients: {
        rank: number;
        name: string;
        total_appointments: string;
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Reports',
        href: route('admin.reports.patient'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container class="space-y-8">
            <!-- Most Loyal Patients -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-4">
                        <CardTitle>Most Loyal Patients</CardTitle>

                        <Button
                            as="a"
                            variant="destructive"
                            :href="route('admin.reports.patient-loyalty.pdf')"
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
                                <TableHead>Rank</TableHead>
                                <TableHead>Patient Name</TableHead>
                                <TableHead class="text-right">Total Appointments</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="patient in mostLoyalPatients"
                                :key="patient.rank"
                            >
                                <TableCell>{{ patient.rank }}</TableCell>
                                <TableCell>{{ patient.name }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ patient.total_appointments }}</TableCell>
                            </TableRow>
                            <TableEmpty
                                v-if="!mostLoyalPatients.length"
                                :colspan="3"
                            >
                                No patient data available
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </Container>
    </AppLayout>
</template>
