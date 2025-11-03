<script setup lang="ts">
import Container from '@/components/Container.vue';
import EditPatientDialog from '@/components/EditPatientDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Patient } from '@/types';
import { Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
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

        <Container>
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

                <!-- Personal Information -->
                <div class="space-y-4 rounded-xl border bg-background p-4">
                    <h3 class="border-b pb-1 text-xs font-semibold text-muted-foreground uppercase">
                        Personal Details
                    </h3>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">First Name</label>
                            <p class="text-sm font-medium">{{ patient.first_name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Last Name</label>
                            <p class="text-sm font-medium">{{ patient.last_name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Middle Name</label>
                            <p class="text-sm font-medium">{{ patient.middle_name ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Contact Number</label>
                            <p class="text-sm font-medium">{{ patient.contact_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Demographics -->
                <div class="space-y-4 rounded-xl border bg-background p-4">
                    <h3 class="border-b pb-1 text-xs font-semibold text-muted-foreground uppercase">Demographics</h3>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Gender</label>
                            <p class="text-sm font-medium capitalize">{{ patient.gender }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Civil Status</label>
                            <p class="text-sm font-medium capitalize">{{ patient.civil_status }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Birthdate</label>
                            <p class="text-sm font-medium">{{ patient.birthdate.formatted_date }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Age</label>
                            <p class="text-sm font-medium">{{ patient.age.formatted_long }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="space-y-4 rounded-xl border bg-background p-4">
                    <h3 class="border-b pb-1 text-xs font-semibold text-muted-foreground uppercase">
                        Contact Information
                    </h3>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Email Address</label>
                            <p class="text-sm font-medium">{{ patient.user?.email }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Address</label>
                            <p class="text-sm font-medium whitespace-pre-wrap">{{ patient.address }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <EditPatientDialog
                v-model:open="isEditPatientDialogOpen"
                :patient="patient"
            />
        </Container>
    </AppLayout>
</template>
