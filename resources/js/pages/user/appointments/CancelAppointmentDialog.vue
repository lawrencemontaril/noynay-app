<script setup lang="ts">
import DataCard from '@/components/DataCard.vue';
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { Appointment } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    appointment: Appointment;
    open: boolean;
}>();
const emit = defineEmits(['update:open']);

const closeDialog = () => {
    emit('update:open', false);
};

const inertiaForm = useInertiaForm({
    status: 'cancelled',
});

const updateAppointment = () => {
    inertiaForm.patch(route('appointments.update', props.appointment?.id), {
        onSuccess: () => {
            closeDialog();
        },
    });
};
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Cancel Appointment #{{ appointment.id }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="updateAppointment">
                <DataCard title="Appointment Date">
                    <p class="text-sm">{{ appointment.scheduled_at.formatted_date }}</p>
                </DataCard>

                <DataCard title="Reason for visit / Complaints">
                    <p class="text-sm">{{ appointment.complaints ?? 'N/A' }}</p>
                </DataCard>

                <DialogFooter>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="inertiaForm.processing"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Cancel appointment
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
