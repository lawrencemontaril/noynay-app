<script setup lang="ts">
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import DataCard from '@/components/DataCard.vue';
import DeleteLaboratoryResultDialog from '@/components/DeleteLaboratoryResultDialog.vue';
import EditLaboratoryResultDialog from '@/components/EditLaboratoryResultDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Activity, Appointment, BreadcrumbItem, LaboratoryResult, Patient } from '@/types';
import { LAB_TYPES } from '@/types/constants';
import { Link } from '@inertiajs/vue3';
import { Archive, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    laboratory_result: LaboratoryResult;
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
        title: 'Laboratory Results',
        href: route('admin.patients.appointments.laboratory_results', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
    {
        title: 'Details',
        href: route('admin.patients.appointments.laboratory_results.show', {
            patient: props.patient.id,
            appointment: props.appointment.id,
            laboratoryResult: props.laboratory_result.id,
        }),
    },
];

const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <PatientProfileTabs :patient="patient" />

        <PatientAppointmentLayout
            :patient-id="patient.id"
            :appointment-id="appointment.id"
        >
            <Button
                class="w-full"
                variant="outline"
                as-child
            >
                <Link
                    :href="
                        route('admin.patients.appointments.laboratory_results', {
                            patient: props.patient.id,
                            appointment: props.appointment.id,
                        })
                    "
                    prefetch
                >
                    Back to list
                </Link>
            </Button>

            <Card class="mx-auto max-w-4xl shadow-xs">
                <!-- Header -->
                <CardHeader class="flex items-center justify-between border-b">
                    <div>
                        <CardTitle class="inline-flex items-center gap-2 text-xl font-semibold">
                            {{ LAB_TYPES.find((type) => type.value === laboratory_result.type)?.label }}
                        </CardTitle>
                    </div>

                    <div class="flex gap-2">
                        <Button
                            v-if="hasPermissionTo('laboratory_resuts:update')"
                            variant="warning"
                            size="icon"
                            @click="isEditDialogOpen = true"
                        >
                            <Pencil />
                        </Button>

                        <Button
                            v-if="hasPermissionTo('laboratory_results:delete')"
                            variant="secondary"
                            size="icon"
                            @click="isDeleteDialogOpen = true"
                        >
                            <Archive />
                        </Button>
                    </div>
                </CardHeader>

                <!-- Body -->
                <CardContent class="text-sm">
                    <!-- Patient Info -->
                    <DataCard
                        title="Patient Information"
                        :columns="3"
                    >
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Name</label>
                            <p class="text-sm font-semibold">
                                {{ getFullName(patient.last_name, patient.first_name, patient.middle_name) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Gender</label>
                            <p class="text-sm capitalize">
                                {{ patient?.gender }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Age</label>
                            <p class="text-sm">
                                {{ patient?.age?.formatted_long }}
                            </p>
                        </div>
                    </DataCard>

                    <DataCard title="Test Type">
                        <p class="text-sm">
                            {{ LAB_TYPES.find((type) => type.value === laboratory_result?.type)?.label }}
                        </p>
                    </DataCard>

                    <DataCard title="Description">
                        <p class="text-sm">{{ laboratory_result?.description ?? 'N/A' }}</p>
                    </DataCard>

                    <iframe
                        v-if="laboratory_result.results_file_url"
                        :src="`${laboratory_result.results_file_url}#navpanes=0`"
                        type="application/pdf"
                        frameborder="0"
                        width="100%"
                        height="600px"
                    ></iframe>
                </CardContent>
            </Card>

            <ActivityTimeline :activities="activities" />

            <EditLaboratoryResultDialog
                v-if="hasPermissionTo('laboratory_results:update')"
                v-model:open="isEditDialogOpen"
                :patient="patient"
                :appointment="appointment"
                :laboratory_result="laboratory_result"
            />

            <DeleteLaboratoryResultDialog
                v-if="hasPermissionTo('laboratory_results:delete')"
                v-model:open="isDeleteDialogOpen"
                :laboratory_result="laboratory_result"
            />
        </PatientAppointmentLayout>
    </AppLayout>
</template>
