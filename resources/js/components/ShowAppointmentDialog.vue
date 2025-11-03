<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Appointment, Patient } from '@/types';
import { ALL_SERVICES } from '@/types/constants';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import * as z from 'zod';
import DataCard from './DataCard.vue';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    appointment: Appointment | null;
}>();
const emit = defineEmits(['update:open']);

const { hasPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    status: props.appointment?.status,
});

const formSchema = toTypedSchema(
    z.object({
        status: z.enum(['pending', 'approved', 'rejected', 'cancelled', 'completed']),
    }),
);

const { handleSubmit, setErrors, setFieldValue, resetForm } = useVeeForm({
    validationSchema: formSchema,
});

const updateAppointment = handleSubmit((validatedValues) => {
    inertiaForm.status = validatedValues.status;

    inertiaForm.patch(route('admin.appointments.update', props.appointment?.id), {
        onError: (serverErrors) => {
            setErrors(serverErrors);
        },
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Appointment #{{ appointment?.id }} </DialogTitle>
            </DialogHeader>

            <!-- Patient Info -->
            <DataCard
                title="Patient Information"
                :columns="2"
            >
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Name</label>
                    <p class="text-sm font-semibold">
                        {{ getFullName(patient?.last_name!, patient?.first_name!, patient?.middle_name!) }}
                    </p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Status</label>
                    <p
                        class="text-sm font-semibold capitalize"
                        :class="{
                            'text-primary': appointment?.status === 'approved' || appointment?.status === 'completed',
                            'text-destructive':
                                appointment?.status === 'rejected' || appointment?.status === 'cancelled',
                            'text-warning': appointment?.status === 'pending',
                        }"
                    >
                        {{ appointment?.status }}
                    </p>
                </div>
            </DataCard>

            <DataCard title="Service Type">
                <p class="text-sm">
                    {{ ALL_SERVICES.find((service) => service.value === appointment?.type)?.label }}
                </p>
            </DataCard>

            <DataCard title="Scheduled Date">
                <p class="text-sm">
                    {{ appointment?.scheduled_at.formatted_date }}
                </p>
            </DataCard>

            <DataCard title="Complaints / Notes">
                <p class="text-sm">
                    {{ appointment?.complaints ?? 'N/A' }}
                </p>
            </DataCard>

            <form @submit.prevent="updateAppointment">
                <DialogFooter>
                    <template v-if="appointment?.status === 'pending' && hasPermissionTo('appointments:update')">
                        <Button
                            type="submit"
                            @click="setFieldValue('status', 'approved')"
                            :disabled="inertiaForm.processing"
                            class="flex items-center gap-2"
                        >
                            <LoaderCircle
                                v-if="inertiaForm.processing && inertiaForm.status === 'approved'"
                                class="h-4 w-4 animate-spin"
                            />
                            Approve
                        </Button>

                        <Button
                            variant="destructive"
                            @click="setFieldValue('status', 'rejected')"
                            :disabled="inertiaForm.processing"
                            class="flex items-center gap-2"
                        >
                            <LoaderCircle
                                v-if="inertiaForm.processing && inertiaForm.status === 'rejected'"
                                class="h-4 w-4 animate-spin"
                            />
                            Reject
                        </Button>
                    </template>

                    <template v-else>
                        <Button
                            v-if="appointment?.status === 'approved' || appointment?.status === 'completed'"
                            disabled
                        >
                            {{ appointment?.status === 'approved' ? 'Approved' : 'Completed' }}
                        </Button>

                        <Button
                            v-if="appointment?.status === 'rejected'"
                            variant="destructive"
                            disabled
                        >
                            Rejected
                        </Button>
                    </template>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
