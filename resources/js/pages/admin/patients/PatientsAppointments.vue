<script setup lang="ts">
import Container from '@/components/Container.vue';
import DeleteAppointmentDialog from '@/components/DeleteAppointmentDialog.vue';
import EditAppointmentDialog from '@/components/EditAppointmentDialog.vue';
import Pagination from '@/components/Pagination.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Appointment, BreadcrumbItem, PaginatedData, Patient } from '@/types';
import { ALL_SERVICES, APPOINTMENT_STATUSES } from '@/types/constants';
import { Link } from '@inertiajs/vue3';
import { Ellipsis, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointments: PaginatedData<Appointment>;
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
];

const selectedAppointment = ref<Appointment | null>(null);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

const openEditDialog = (appointment: Appointment) => {
    selectedAppointment.value = appointment;
    isEditDialogOpen.value = true;
};

const openDeleteDialog = (appointment: Appointment) => {
    selectedAppointment.value = appointment;
    isDeleteDialogOpen.value = true;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <PatientProfileTabs :patient="patient" />

        <Container>
            <div class="space-y-4">
                <div
                    v-for="appointment in appointments.data"
                    :key="appointment.id"
                    class="rounded-xl border bg-card shadow-xs"
                >
                    <!-- Header -->
                    <div class="flex flex-col gap-2 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                            <h3 class="text-base leading-tight font-semibold text-foreground">
                                {{ ALL_SERVICES.find((service) => service.value === appointment.type)?.label }}
                                Appointment
                            </h3>

                            <Badge
                                as="span"
                                :variant="
                                    APPOINTMENT_STATUSES.find((status) => status.value === appointment.status)?.badge
                                "
                                class="mt-1 w-fit capitalize sm:mt-0"
                            >
                                {{ appointment.status }}
                            </Badge>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex flex-wrap gap-1 sm:gap-2">
                            <Button
                                v-if="hasPermissionTo('appointments:view')"
                                variant="outline"
                                size="sm"
                                class="flex items-center gap-1 text-xs"
                                as-child
                            >
                                <Link
                                    :href="
                                        route('admin.patients.appointments.show', {
                                            patient: patient.id,
                                            appointment: appointment.id,
                                        })
                                    "
                                    prefetch
                                >
                                    <Ellipsis class="h-4 w-4" /> Details
                                </Link>
                            </Button>

                            <Button
                                v-if="hasPermissionTo('appointments:update')"
                                @click="openEditDialog(appointment)"
                                variant="warning"
                                size="icon"
                                class="h-8 w-8"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>

                            <Button
                                v-if="hasPermissionTo('appointments:delete')"
                                @click="openDeleteDialog(appointment)"
                                variant="destructive"
                                size="icon"
                                class="h-8 w-8"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-4 py-3 text-sm text-muted-foreground">
                        <p v-if="appointment.complaints">
                            <span class="font-medium text-foreground">Complaints:</span>
                            {{ appointment.complaints }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Scheduled for:
                            <span class="font-medium text-foreground">{{
                                appointment.scheduled_at.formatted_date
                            }}</span>
                        </p>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!appointments.data.length"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/20 p-8 text-center"
                >
                    <p class="text-sm text-muted-foreground">No appointments yet.</p>
                </div>

                <Pagination :meta="appointments.meta" />

                <EditAppointmentDialog
                    v-model:open="isEditDialogOpen"
                    :patient="patient"
                    :appointment="selectedAppointment"
                />
                <DeleteAppointmentDialog
                    v-model:open="isDeleteDialogOpen"
                    :appointment="selectedAppointment"
                />
            </div>
        </Container>
    </AppLayout>
</template>
