<script setup lang="ts">
import CreateConsultationDialog from '@/components/CreateConsultationDialog.vue';
import DeleteConsultationDialog from '@/components/DeleteConsultationDialog.vue';
import EditConsultationDialog from '@/components/EditConsultationDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import ShowConsultationDialog from '@/components/ShowConsultationDialog.vue';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Appointment, BreadcrumbItem, Consultation, Patient } from '@/types';
import { CONSULTATION_TYPES } from '@/types/constants';
import { Ellipsis, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    consultations: Consultation[];
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
        title: 'Consultations',
        href: route('admin.patients.appointments.consultations', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
];

const selectedConsultation = ref<Consultation | null>(null);
const isCreateDialogOpen = ref(false);
const isShowDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

const openShowDialog = (consultation: Consultation) => {
    selectedConsultation.value = consultation;
    isShowDialogOpen.value = true;
};

const openEditDialog = (consultation: Consultation) => {
    selectedConsultation.value = consultation;
    isEditDialogOpen.value = true;
};

const openDeleteDialog = (consultation: Consultation) => {
    selectedConsultation.value = consultation;
    isDeleteDialogOpen.value = true;
};
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
                    v-if="hasPermissionTo('consultations:create') && appointment.is_operatable"
                    @click="isCreateDialogOpen = true"
                    class="w-full"
                >
                    Add consultation
                </Button>

                <div
                    v-for="consultation in consultations"
                    :key="consultation.id"
                    class="rounded-xl border bg-card shadow-xs"
                >
                    <!-- Header -->
                    <div class="flex flex-col gap-2 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                            <h3 class="text-base leading-tight font-semibold text-foreground">
                                {{ CONSULTATION_TYPES.find((service) => service.value === consultation.type)?.label }}
                            </h3>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex flex-wrap gap-1 sm:gap-2">
                            <Button
                                v-if="hasPermissionTo('consultations:view')"
                                @click="openShowDialog(consultation)"
                                variant="outline"
                                size="sm"
                                class="flex items-center gap-1 text-xs"
                            >
                                <Ellipsis class="h-4 w-4" /> Details
                            </Button>

                            <Button
                                v-if="hasPermissionTo('consultations:update')"
                                @click="openEditDialog(consultation)"
                                variant="warning"
                                size="icon"
                                class="h-8 w-8"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>

                            <Button
                                v-if="hasPermissionTo('consultations:delete')"
                                @click="openDeleteDialog(consultation)"
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
                            {{ consultation.chief_complaints }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Consultation date:
                            <span class="font-medium text-foreground">{{
                                consultation.created_at.formatted_date
                            }}</span>
                        </p>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!consultations.length"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/20 p-8 text-center"
                >
                    <p class="text-sm text-muted-foreground">No consultations yet.</p>
                </div>

                <CreateConsultationDialog
                    v-if="hasPermissionTo('consultations:create')"
                    v-model:open="isCreateDialogOpen"
                    :patient="patient"
                    :appointment="appointment"
                    is-type-editable
                />

                <ShowConsultationDialog
                    v-if="hasPermissionTo('consultations:view')"
                    v-model:open="isShowDialogOpen"
                    :consultation="selectedConsultation"
                    :patient="patient"
                />

                <EditConsultationDialog
                    v-if="hasPermissionTo('consultations:update')"
                    v-model:open="isEditDialogOpen"
                    :patient="patient"
                    :appointment="appointment"
                    :consultation="selectedConsultation"
                />

                <DeleteConsultationDialog
                    v-if="hasPermissionTo('consultations:delete')"
                    v-model:open="isDeleteDialogOpen"
                    :consultation="selectedConsultation"
                />
            </div>
        </PatientAppointmentLayout>
    </AppLayout>
</template>
