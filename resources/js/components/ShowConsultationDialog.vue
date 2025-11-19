<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { useFormatters } from '@/composables/useFormatters';
import { Consultation, Patient } from '@/types';
import { CONSULTATION_TYPES } from '@/types/constants';
import { computed } from 'vue';
import { DataCard, DataCell, DataLabel, DataText } from './ui/data';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    consultation: Consultation | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

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
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Consultation #{{ consultation?.id }}</DialogTitle>
            </DialogHeader>

            <DataCard
                title="Patient Information"
                :columns="3"
            >
                <DataCell>
                    <DataLabel>Name</DataLabel>
                    <DataText>
                        {{ getFullName(patient?.last_name!, patient?.first_name!, patient?.middle_name!) }}
                    </DataText>
                </DataCell>
                <DataCell>
                    <DataLabel>Gender</DataLabel>
                    <DataText>
                        {{ patient?.gender }}
                    </DataText>
                </DataCell>
                <DataCell>
                    <DataLabel>Age</DataLabel>
                    <DataText>
                        {{ patient?.age?.formatted_long }}
                    </DataText>
                </DataCell>
            </DataCard>

            <DataCard
                title="Type"
                :columns="1"
            >
                <DataText>
                    {{ CONSULTATION_TYPES.find((type) => type.value === consultation?.type)?.label }}
                </DataText>
            </DataCard>

            <DataCard
                title="Chief Complaints"
                :columns="1"
            >
                <DataText>{{ consultation?.chief_complaints }}</DataText>
            </DataCard>

            <DataCard
                title="Assessment"
                :columns="1"
            >
                <DataText>{{ consultation?.assessment }}</DataText>
            </DataCard>

            <DataCard
                title="Plan"
                :columns="1"
            >
                <DataText>{{ consultation?.plan }}</DataText>
            </DataCard>

            <DataCard
                title="Vital Signs"
                :columns="3"
            >
                <DataCell>
                    <DataLabel>Blood Pressure</DataLabel>
                    <DataText>
                        <template v-if="consultation?.systolic != null && consultation?.diastolic != null">
                            {{ consultation.systolic }}/{{ consultation.diastolic }}
                        </template>
                        <template v-else>N/A</template>
                    </DataText>
                </DataCell>

                <DataCell>
                    <DataLabel>Heart Rate</DataLabel>
                    <DataText>
                        {{ consultation?.heart_rate != null ? consultation.heart_rate + ' bpm' : 'N/A' }}
                    </DataText>
                </DataCell>

                <DataCell>
                    <DataLabel>Respiratory Rate</DataLabel>
                    <DataText>
                        {{ consultation?.respiratory_rate != null ? consultation.respiratory_rate + ' bpm' : 'N/A' }}
                    </DataText>
                </DataCell>
            </DataCard>

            <!-- BODY MEASUREMENTS -->
            <DataCard
                title="Body Measurements"
                :columns="3"
            >
                <DataCell>
                    <DataLabel>Weight (kg)</DataLabel>
                    <DataText>
                        {{ consultation?.weight_kg != null ? consultation.weight_kg + ' kg' : 'N/A' }}
                    </DataText>
                </DataCell>

                <DataCell>
                    <DataLabel>Height (cm)</DataLabel>
                    <DataText>
                        {{ consultation?.height_cm != null ? consultation.height_cm + ' cm' : 'N/A' }}
                    </DataText>
                </DataCell>

                <DataCell>
                    <DataLabel>BMI</DataLabel>
                    <DataText>
                        {{ bmi ? bmi + ' ; ' + bmiCategory : 'N/A' }}
                    </DataText>
                </DataCell>
            </DataCard>

            <!-- OTHER READINGS -->
            <DataCard
                title="Additional Readings"
                :columns="2"
            >
                <DataCell>
                    <DataLabel>Temperature</DataLabel>
                    <DataText>
                        {{ consultation?.temperature_c != null ? consultation.temperature_c + '°C' : 'N/A' }}
                    </DataText>
                </DataCell>

                <DataCell>
                    <DataLabel>Oxygen Saturation</DataLabel>
                    <DataText>
                        {{ consultation?.oxygen_saturation != null ? consultation.oxygen_saturation + '%' : 'N/A' }}
                    </DataText>
                </DataCell>
            </DataCard>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="closeDialog"
                >
                    Close
                </Button>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>
