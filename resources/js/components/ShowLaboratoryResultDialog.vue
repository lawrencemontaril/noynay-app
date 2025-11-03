<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { useFormatters } from '@/composables/useFormatters';
import { LaboratoryResult, Patient } from '@/types';
import { LAB_TYPES } from '@/types/constants';
import DataCard from './DataCard.vue';

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

            <DataCard title="Test Type">
                <p class="text-sm">{{ LAB_TYPES.find((type) => type.value === laboratory_result?.type)?.label }}</p>
            </DataCard>

            <DataCard title="Description">
                <p class="text-sm">{{ laboratory_result?.description ?? 'N/A' }}</p>
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
