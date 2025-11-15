<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    mostLoyalPatients: Array<{
        id: number;
        rank?: number;
        name: string;
        total_appointments: number;
        visits_per_year: number;
        last_visit: string | null;
        tenure_years: number;
        total_no_shows: number;
        distinct_services: number;
        total_spend: number;
        avg_days_between_visits: number | null;
        status_score: number;
        loyalty_score: number;
    }>;
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
                <CardHeader class="flex items-center justify-between gap-4">
                    <CardTitle>Most Loyal Patients</CardTitle>

                    <Button
                        as="a"
                        variant="destructive"
                        :href="route('admin.reports.patient-loyalty.pdf')"
                        target="_blank"
                    >
                        Download PDF
                    </Button>
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[60px]">Rank</TableHead>
                                <TableHead>Patient</TableHead>
                                <TableHead class="text-right">Visits</TableHead>
                                <TableHead class="text-right">Visits/yr</TableHead>
                                <TableHead class="text-right">Tenure (yrs)</TableHead>
                                <TableHead class="text-right">Services</TableHead>
                                <TableHead class="text-right">Spend</TableHead>
                                <TableHead class="text-right">Avg Days</TableHead>
                                <TableHead class="text-right">Status Score</TableHead>
                                <TableHead class="text-right">Loyalty Score</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="(p, idx) in mostLoyalPatients"
                                :key="p.id"
                            >
                                <TableCell>{{ idx + 1 }}</TableCell>
                                <TableCell>{{ p.name }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ p.total_appointments }}</TableCell>
                                <TableCell class="text-right">{{ p.visits_per_year }}</TableCell>
                                <TableCell class="text-right">{{ p.tenure_years }}</TableCell>
                                <TableCell class="text-right">{{ p.distinct_services }}</TableCell>
                                <TableCell class="text-right">{{
                                    new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(
                                        p.total_spend,
                                    )
                                }}</TableCell>
                                <TableCell class="text-right">{{ p.avg_days_between_visits ?? '—' }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ p.status_score }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ p.loyalty_score }}</TableCell>
                            </TableRow>

                            <TableRow v-if="!mostLoyalPatients.length">
                                <TableCell
                                    colspan="11"
                                    class="py-4 text-center text-muted-foreground"
                                    >No patient data available.</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </Container>
    </AppLayout>
</template>
