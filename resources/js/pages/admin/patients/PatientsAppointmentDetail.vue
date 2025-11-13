<script setup lang="ts">
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import DataCard from '@/components/DataCard.vue';
import DataCell from '@/components/DataCell.vue';
import DataLabel from '@/components/DataLabel.vue';
import DataText from '@/components/DataText.vue';
import EditAppointmentDialog from '@/components/EditAppointmentDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Activity, Appointment, BreadcrumbItem, Patient } from '@/types';
import { ALL_SERVICES, APPOINTMENT_STATUSES } from '@/types/constants';
import { Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    activities?: Activity[];
}>();

const { hasPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: route('admin.patients.index'),
    },
    {
        title: getFullName(props.patient.last_name, props.patient.first_name, props.patient.middle_name),
        href: route('admin.patients.show', props.patient.id),
    },
    {
        title: 'Appointments',
        href: route('admin.patients.appointments', props.patient.id),
    },
    {
        title: 'Details',
        href: route('admin.patients.appointments.show', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
];

const isEditDialogOpen = ref(false);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <PatientProfileTabs :patient="patient" />

        <PatientAppointmentLayout
            :patient-id="patient.id"
            :appointment-id="appointment.id"
        >
            <Card class="mx-auto max-w-4xl shadow-xs">
                <!-- Header -->
                <CardHeader class="flex items-center justify-between border-b">
                    <div>
                        <CardTitle class="inline-flex items-center gap-2 text-xl font-semibold capitalize">
                            {{ ALL_SERVICES.find((service) => service.value === appointment.type)?.label }} appointment
                            <Badge
                                :variant="
                                    APPOINTMENT_STATUSES.find((status) => status.value === appointment.status)?.badge
                                "
                                class="capitalize"
                            >
                                {{ APPOINTMENT_STATUSES.find((status) => status.value === appointment.status)?.label }}
                            </Badge>
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">
                            Scheduled on {{ appointment.scheduled_at.formatted_date }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Button
                            v-if="hasPermissionTo('update appointments')"
                            size="sm"
                            variant="outline"
                            @click="isEditDialogOpen = true"
                        >
                            <Pencil class="mr-1 h-4 w-4" />
                            Edit
                        </Button>
                    </div>
                </CardHeader>

                <!-- Body -->
                <CardContent class="text-sm">
                    <DataCard title="Appointment Details">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <DataCell>
                                <DataLabel>Service Type</DataLabel>
                                <DataText>
                                    {{ ALL_SERVICES.find((service) => service.value === appointment.type)?.label }}
                                </DataText>
                            </DataCell>
                            <DataCell>
                                <DataLabel>Status</DataLabel>
                                <DataText>
                                    {{
                                        APPOINTMENT_STATUSES.find((status) => status.value === appointment.status)
                                            ?.label
                                    }}
                                </DataText>
                            </DataCell>
                            <DataCell>
                                <DataLabel>Schedule</DataLabel>
                                <DataText>
                                    {{ appointment.scheduled_at.formatted_date }}
                                </DataText>
                            </DataCell>
                            <DataCell>
                                <DataLabel>Creation Date</DataLabel>
                                <DataText>
                                    {{ appointment.created_at.formatted_date }}
                                </DataText>
                            </DataCell>
                        </div>
                    </DataCard>

                    <DataCard title="Complaints">
                        <DataText>{{ appointment.complaints || '—' }}</DataText>
                    </DataCard>
                </CardContent>
            </Card>

            <ActivityTimeline :activities="activities" />

            <EditAppointmentDialog
                v-model:open="isEditDialogOpen"
                :patient="patient"
                :appointment="appointment"
            />
        </PatientAppointmentLayout>
    </AppLayout>
</template>
template
