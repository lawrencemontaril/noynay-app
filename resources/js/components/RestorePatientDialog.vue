<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { DataCard, DataCell, DataLabel } from './ui/data';

const props = defineProps<{
    open: boolean;
    patient: Patient | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

const closeDialog = () => {
    emit('update:open', false);
};

const inertiaForm = useInertiaForm({});

const restorePatient = () => {
    inertiaForm.patch(route('admin.patients.restore', props.patient?.id), {
        onSuccess: () => {
            closeDialog();
        },
    });
};
</script>

<template>
    <AlertDialog
        :open="open"
        @update:open="closeDialog"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you sure you want to restore this patient?</AlertDialogTitle>
            </AlertDialogHeader>

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

            <DataCard title="Archive date">
                <DataText>{{ patient?.deleted_at?.formatted_date }}</DataText>
            </DataCard>

            <AlertDialogFooter>
                <Button
                    variant="outline"
                    :disabled="inertiaForm.processing"
                    @click="closeDialog"
                >
                    Cancel
                </Button>

                <Button
                    type="submit"
                    :disabled="inertiaForm.processing"
                    @click="restorePatient"
                >
                    <LoaderCircle
                        v-if="inertiaForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Restore patient
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
