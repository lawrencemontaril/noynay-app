<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { useFormatters } from '@/composables/useFormatters';
import { Consultation, Patient } from '@/types';
import { CONSULTATION_TYPES } from '@/types/constants';
import { computed } from 'vue';
import DataCard from './DataCard.vue';

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
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Name</label>
                    <p class="text-sm font-semibold">
                        {{ getFullName(patient?.last_name!, patient?.first_name!, patient?.middle_name!) }}
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
                <p class="text-sm">{{ consultation?.chief_complaints }}</p>
            </DataCard>

            <DataCard
                title="Assessment"
                :columns="1"
            >
                <p class="text-sm">{{ consultation?.assessment }}</p>
            </DataCard>

            <DataCard
                title="Plan"
                :columns="1"
            >
                <p class="text-sm">{{ consultation?.plan }}</p>
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
                        {{ consultation?.respiratory_rate != null ? consultation.respiratory_rate + ' bpm' : 'N/A' }}
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
                        {{ consultation?.oxygen_saturation != null ? consultation.oxygen_saturation + '%' : 'N/A' }}
                    </p>
                </div>
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
