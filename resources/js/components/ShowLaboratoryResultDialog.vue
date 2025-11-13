<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { useFormatters } from '@/composables/useFormatters';
import { LaboratoryResult, Patient } from '@/types';
import { LAB_TYPES, PATIENT_GENDERS } from '@/types/constants';
import DataCard from './DataCard.vue';
import DataCell from './DataCell.vue';
import DataLabel from './DataLabel.vue';
import DataText from './DataText.vue';

defineProps<{
    open: boolean;
    patient?: Patient | null;
    laboratory_result: LaboratoryResult | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Laboratory Result #{{ laboratory_result?.id }}</DialogTitle>
            </DialogHeader>

            <!-- Patient Info -->
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
                        {{ PATIENT_GENDERS.find((gender) => gender.value === patient?.gender)?.label }}
                    </DataText>
                </DataCell>
                <DataCell>
                    <DataLabel>Age</DataLabel>
                    <DataText>
                        {{ patient?.age?.formatted_long }}
                    </DataText>
                </DataCell>
            </DataCard>

            <DataCard title="Test Type">
                <DataText>{{ LAB_TYPES.find((type) => type.value === laboratory_result?.type)?.label }}</DataText>
            </DataCard>

            <DataCard title="Description">
                <DataText>{{ laboratory_result?.description ?? 'N/A' }}</DataText>
            </DataCard>

            <DialogFooter>
                <Button
                    :disabled="!laboratory_result?.results_file_url"
                    class="p-0"
                >
                    <a
                        :href="laboratory_result?.results_file_url ?? '#'"
                        target="_blank"
                        class="px-4"
                    >
                        View results
                    </a>
                </Button>

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
