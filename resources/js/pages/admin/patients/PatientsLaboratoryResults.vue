<script setup lang="ts">
import CreateLaboratoryResultDialog from '@/components/CreateLaboratoryResultDialog.vue';
import DeleteLaboratoryResultDialog from '@/components/DeleteLaboratoryResultDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import RequestLaboratoryResultDialog from '@/components/RequestLaboratoryResultDialog.vue';
import ShowLaboratoryResultDialog from '@/components/ShowLaboratoryResultDialog.vue';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Appointment, BreadcrumbItem, LaboratoryResult, Patient } from '@/types';
import { LAB_TYPES } from '@/types/constants';
import { Ellipsis, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    laboratory_results: LaboratoryResult[];
}>();

const { getFullName } = useFormatters();
const { hasRole, hasPermissionTo } = usePermissions();

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
        title: 'Laboratory Results',
        href: route('admin.patients.appointments.laboratory_results', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
];

const selectedLaboratoryResult = ref<LaboratoryResult | null>(null);
const isCreateDialogOpen = ref(false);
const isRequestDialogOpen = ref(false);
const isShowDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

const openShowDialog = (laboratory_result: LaboratoryResult) => {
    selectedLaboratoryResult.value = laboratory_result;
    isShowDialogOpen.value = true;
};

const openDeleteDialog = (laboratory_result: LaboratoryResult) => {
    selectedLaboratoryResult.value = laboratory_result;
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
                    v-if="hasRole('doctor') && appointment.is_operatable"
                    @click="isRequestDialogOpen = true"
                    class="w-full"
                >
                    Request laboratory
                </Button>

                <Button
                    v-if="hasRole('laboratory_staff') && appointment.is_operatable"
                    @click="isCreateDialogOpen = true"
                    class="w-full"
                >
                    Add laboratory result
                </Button>

                <div
                    v-for="laboratory_result in laboratory_results"
                    :key="laboratory_result.id"
                    class="rounded-xl border bg-card shadow-xs"
                >
                    <!-- Header -->
                    <div class="flex flex-col gap-2 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2">
                            <h3 class="text-base leading-tight font-semibold text-foreground">
                                {{ LAB_TYPES.find((type) => type.value === laboratory_result.type)?.label }}
                            </h3>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex flex-wrap gap-1 sm:gap-2">
                            <Button
                                v-if="hasPermissionTo('laboratory_results:view')"
                                @click="openShowDialog(laboratory_result)"
                                variant="outline"
                                size="sm"
                                class="flex items-center gap-1 text-xs"
                            >
                                <Ellipsis class="h-4 w-4" /> Details
                            </Button>

                            <Button
                                v-if="hasPermissionTo('laboratory_results:delete')"
                                @click="openDeleteDialog(laboratory_result)"
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
                        <p>
                            <span class="font-medium text-foreground">Description:</span>
                            {{ laboratory_result.description ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!laboratory_results.length"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/20 p-8 text-center"
                >
                    <p class="text-sm text-muted-foreground">No laboratory results yet.</p>
                </div>

                <CreateLaboratoryResultDialog
                    v-if="hasPermissionTo('laboratory_results:create') && hasRole('laboratory_staff')"
                    v-model:open="isCreateDialogOpen"
                    :patient="patient"
                    :appointment="appointment"
                    is-type-editable
                />

                <RequestLaboratoryResultDialog
                    v-if="hasRole('doctor')"
                    v-model:open="isRequestDialogOpen"
                    :patient="patient"
                    :appointment="appointment"
                />

                <ShowLaboratoryResultDialog
                    v-if="hasPermissionTo('laboratory_results:view')"
                    v-model:open="isShowDialogOpen"
                    :patient="patient"
                    :laboratory_result="selectedLaboratoryResult"
                />

                <DeleteLaboratoryResultDialog
                    v-if="hasPermissionTo('laboratory_results:delete')"
                    v-model:open="isDeleteDialogOpen"
                    :laboratory_result="selectedLaboratoryResult"
                />
            </div>
        </PatientAppointmentLayout>
    </AppLayout>
</template>
