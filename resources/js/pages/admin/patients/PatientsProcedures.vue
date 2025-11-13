<script setup lang="ts">
import CreateProcedureDialog from '@/components/CreateProcedureDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Appointment, BreadcrumbItem, Invoice, Patient, Procedure } from '@/types';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    invoice: Invoice | null;
    procedures: Procedure[];
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
        title: 'Procedures',
        href: route('admin.patients.appointments.procedures', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
];

const isCreateDialogOpen = ref(false);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <PatientProfileTabs :patient="patient" />

        <PatientAppointmentLayout
            :patient-id="patient.id"
            :appointment-id="appointment.id"
        >
            <div class="space-y-4">
                <Button
                    v-if="hasPermissionTo('procedures:create') && appointment.is_operatable && !invoice"
                    @click="isCreateDialogOpen = true"
                    class="w-full"
                >
                    Add procedure
                </Button>

                <!-- Procedure List -->
                <div
                    v-if="procedures.length"
                    class="space-y-3"
                >
                    <div
                        v-for="(procedure, index) in procedures"
                        :key="procedure.id"
                        class="flex items-center justify-between rounded-lg border bg-card px-4 py-3 shadow-sm transition-shadow hover:shadow"
                    >
                        <p class="text-sm font-medium">{{ index + 1 }}. {{ procedure.description }}</p>
                        <span class="text-sm text-muted-foreground">x{{ procedure.quantity }}</span>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!procedures.length"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/20 p-8 text-center"
                >
                    <p class="text-sm text-muted-foreground">No procedures yet.</p>
                </div>

                <CreateProcedureDialog
                    v-if="hasPermissionTo('procedures:create')"
                    v-model:open="isCreateDialogOpen"
                    :patient="patient"
                    :appointment="appointment"
                />
            </div>
        </PatientAppointmentLayout>
    </AppLayout>
</template>
