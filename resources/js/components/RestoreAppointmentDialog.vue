<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import Button from '@/components/ui/button/Button.vue';
import { Appointment } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import DataCard from './DataCard.vue';

interface Props {
    open: boolean;
    appointment: Appointment | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:open']);

const closeDialog = () => {
    emit('update:open', false);
};

const inertiaForm = useInertiaForm({});

const restoreAppointment = () => {
    inertiaForm.patch(route('admin.appointments.restore', props.appointment?.id), {
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
                <AlertDialogTitle>Are you sure you want to restore this appointment?</AlertDialogTitle>
            </AlertDialogHeader>

            <DataCard title="Archive date">
                <p class="text-sm">{{ appointment?.deleted_at?.formatted_date }}</p>
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
                    @click="restoreAppointment"
                >
                    <LoaderCircle
                        v-if="inertiaForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Restore appointment
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
