<script setup lang="ts">
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import Container from '@/components/Container.vue';
import EditPatientDialog from '@/components/EditPatientDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import Button from '@/components/ui/button/Button.vue';
import { DataCard, DataCell, DataLabel, DataText } from '@/components/ui/data';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Activity, BreadcrumbItem, Patient } from '@/types';
import { PATIENT_CIVIL_STATUSES, PATIENT_GENDERS } from '@/types/constants';
import { Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    activities?: Activity[];
}>();

const { getFullName } = useFormatters();
const { hasPermissionTo } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: route('admin.patients.index'),
    },
    {
        title: getFullName(props.patient.last_name, props.patient.first_name, props.patient.middle_name),
        href: route('admin.patients.show', props.patient.id),
    },
];

const isEditPatientDialogOpen = ref(false);

function openEditPatientDialog() {
    isEditPatientDialogOpen.value = true;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <PatientProfileTabs :patient="patient" />

        <Container class="space-y-4">
            <div class="space-y-4 rounded-xl border bg-muted/40 p-6 shadow-sm">
                <!-- Header / Edit button -->
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold tracking-tight">Patient Information</h2>
                    <Button
                        v-if="hasPermissionTo('patients:update')"
                        variant="warning"
                        @click="openEditPatientDialog"
                        class="flex items-center gap-2"
                    >
                        <Pencil class="h-4 w-4" />
                        Edit Patient Information
                    </Button>
                </div>

                <DataCard
                    title="Personal Details"
                    :columns="4"
                >
                    <DataCell>
                        <DataLabel>First Name</DataLabel>
                        <DataText>{{ patient.first_name }}</DataText>
                    </DataCell>

                    <DataCell>
                        <DataLabel>Last Name</DataLabel>
                        <DataText>{{ patient.last_name }}</DataText>
                    </DataCell>

                    <DataCell>
                        <DataLabel>Middle Name</DataLabel>
                        <DataText>{{ patient.middle_name ?? 'N/A' }}</DataText>
                    </DataCell>

                    <DataCell>
                        <DataLabel>Contact Number</DataLabel>
                        <DataText>{{ patient.contact_number }}</DataText>
                    </DataCell>
                </DataCard>

                <!-- Demographics -->
                <DataCard
                    title="Demographics"
                    :columns="4"
                >
                    <DataCell>
                        <DataLabel>Gender</DataLabel>
                        <DataText>{{
                            PATIENT_GENDERS.find((gender) => gender.value === patient.gender)?.label
                        }}</DataText>
                    </DataCell>

                    <DataCell>
                        <DataLabel>Civil Status</DataLabel>
                        <DataText>{{
                            PATIENT_CIVIL_STATUSES.find((civil_status) => civil_status.value === patient.civil_status)
                                ?.label
                        }}</DataText>
                    </DataCell>

                    <DataCell>
                        <DataLabel>Birthdate</DataLabel>
                        <DataText>{{ patient.birthdate.formatted_date }}</DataText>
                    </DataCell>

                    <DataCell>
                        <DataLabel>Age</DataLabel>
                        <DataText>{{ patient.age.formatted_long }}</DataText>
                    </DataCell>
                </DataCard>

                <!-- Contact Info -->
                <DataCard
                    title="Contact Information"
                    :columns="2"
                >
                    <DataCell>
                        <DataLabel>Email Address</DataLabel>
                        <DataText>{{ patient.user?.email }}</DataText>
                    </DataCell>
                    <DataCell>
                        <DataLabel>Address</DataLabel>
                        <DataText>{{ patient.address }}</DataText>
                    </DataCell>
                </DataCard>
            </div>

            <ActivityTimeline :activities="activities" />

            <EditPatientDialog
                v-model:open="isEditPatientDialogOpen"
                :patient="patient"
            />
        </Container>
    </AppLayout>
</template>
