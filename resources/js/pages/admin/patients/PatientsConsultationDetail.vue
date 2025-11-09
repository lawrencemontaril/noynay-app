<script setup lang="ts">
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import DataCard from '@/components/DataCard.vue';
import DeleteConsultationDialog from '@/components/DeleteConsultationDialog.vue';
import EditConsultationDialog from '@/components/EditConsultationDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Activity, Appointment, BreadcrumbItem, Consultation, Patient } from '@/types';
import { CONSULTATION_TYPES } from '@/types/constants';
import { Link } from '@inertiajs/vue3';
import { Archive, Pencil } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    consultation: Consultation;
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
        title: 'Consultations',
        href: route('admin.patients.appointments.consultations', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
    {
        title: 'Details',
        href: route('admin.patients.appointments.consultations.show', {
            patient: props.patient.id,
            appointment: props.appointment.id,
            consultation: props.consultation.id,
        }),
    },
];

const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

const bmi = computed(() => {
    if (!props.consultation?.weight_kg || !props.consultation?.height_cm) return '';
    const h = props.consultation?.height_cm / 100;
    return +(props.consultation?.weight_kg / (h * h)).toFixed(1);
});

const bmiCategory = computed(() => {
    if (!bmi.value) return '';
    if (bmi.value < 18.5) return 'Underweight';
    if (bmi.value < 25) return 'Normal';
    if (bmi.value < 30) return 'Overweight';
    if (bmi.value < 35) return 'Obese (Class I)';
    if (bmi.value < 40) return 'Obese (Class II)';
    return 'Obese (Class III)';
});
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
                        route('admin.patients.appointments.consultations', {
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
                            {{ CONSULTATION_TYPES.find((type) => type.value === consultation.type)?.label }}
                            consultation
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">
                            Consulted on {{ consultation.created_at.formatted_date }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Button
                            v-if="hasPermissionTo('consultations:update')"
                            variant="warning"
                            size="icon"
                            @click="isEditDialogOpen = true"
                        >
                            <Pencil />
                        </Button>

                        <Button
                            v-if="hasPermissionTo('consultations:delete')"
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
                                {{ patient.gender }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Age</label>
                            <p class="text-sm">
                                {{ patient.age.formatted_long }}
                            </p>
                        </div>
                    </DataCard>

                    <DataCard
                        title="Type"
                        :columns="1"
                    >
                        <p class="text-sm">
                            {{ CONSULTATION_TYPES.find((type) => type.value === consultation?.type)?.label }}
                        </p>
                    </DataCard>

                    <DataCard
                        title="Chief Complaints"
                        :columns="1"
                    >
                        <p class="text-sm">{{ consultation.chief_complaints }}</p>
                    </DataCard>

                    <DataCard
                        title="Assessment"
                        :columns="1"
                    >
                        <p class="text-sm">{{ consultation.assessment }}</p>
                    </DataCard>

                    <DataCard
                        title="Plan"
                        :columns="1"
                    >
                        <p class="text-sm">{{ consultation.plan }}</p>
                    </DataCard>

                    <DataCard
                        title="Vital Signs"
                        :columns="3"
                    >
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Blood Pressure</label>
                            <p class="text-sm font-semibold">
                                <template v-if="consultation?.systolic != null && consultation?.diastolic != null">
                                    {{ consultation.systolic }}/{{ consultation.diastolic }}
                                </template>
                                <template v-else>N/A</template>
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Heart Rate</label>
                            <p class="text-sm font-semibold">
                                {{ consultation?.heart_rate != null ? consultation.heart_rate + ' bpm' : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Respiratory Rate</label>
                            <p class="text-sm font-semibold">
                                {{
                                    consultation?.respiratory_rate != null
                                        ? consultation.respiratory_rate + ' bpm'
                                        : 'N/A'
                                }}
                            </p>
                        </div>
                    </DataCard>

                    <!-- BODY MEASUREMENTS -->
                    <DataCard
                        title="Body Measurements"
                        :columns="3"
                    >
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Weight (kg)</label>
                            <p class="text-sm font-semibold">
                                {{ consultation?.weight_kg != null ? consultation.weight_kg + ' kg' : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Height (cm)</label>
                            <p class="text-sm font-semibold">
                                {{ consultation?.height_cm != null ? consultation.height_cm + ' cm' : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">BMI</label>
                            <p class="text-sm font-semibold capitalize">
                                {{ bmi ? bmi + ' ; ' + bmiCategory : 'N/A' }}
                            </p>
                        </div>
                    </DataCard>

                    <!-- OTHER READINGS -->
                    <DataCard
                        title="Additional Readings"
                        :columns="2"
                    >
                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Temperature</label>
                            <p class="text-sm font-semibold">
                                {{ consultation?.temperature_c != null ? consultation.temperature_c + '°C' : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-muted-foreground">Oxygen Saturation</label>
                            <p class="text-sm font-semibold">
                                {{
                                    consultation?.oxygen_saturation != null
                                        ? consultation.oxygen_saturation + '%'
                                        : 'N/A'
                                }}
                            </p>
                        </div>
                    </DataCard>
                </CardContent>
            </Card>

            <ActivityTimeline :activities="activities" />

            <EditConsultationDialog
                v-if="hasPermissionTo('consultations:update')"
                v-model:open="isEditDialogOpen"
                :patient="patient"
                :appointment="appointment"
                :consultation="consultation"
            />

            <DeleteConsultationDialog
                v-if="hasPermissionTo('consultations:delete')"
                v-model:open="isDeleteDialogOpen"
                :consultation="consultation"
            />
        </PatientAppointmentLayout>
    </AppLayout>
</template>
template
