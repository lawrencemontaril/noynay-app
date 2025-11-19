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
import { DataCard, DataCell, DataLabel, DataText } from './ui/data';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    appointment: Appointment | null;
}>();
const emit = defineEmits(['update:open']);

const { hasRole, hasAnyRole, hasPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({});

const formSchema = toTypedSchema(
    z.object({
        status: z.enum(['pending', 'approved', 'rejected', 'cancelled', 'no_show', 'completed']),
    }),
);

const { handleSubmit, setFieldValue, resetForm, values } = useVeeForm({
    validationSchema: formSchema,
});

const approveAppointment = handleSubmit(() => {
    inertiaForm.patch(route('admin.appointments.approve', props.appointment?.id), {
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

const rejectAppointment = handleSubmit(() => {
    inertiaForm.patch(route('admin.appointments.reject', props.appointment?.id), {
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

const noShowAppointment = handleSubmit(() => {
    inertiaForm.patch(route('admin.appointments.noShow', props.appointment?.id), {
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

            <div class="space-y-2">
                <DataCard
                    title="Patient Information"
                    :columns="2"
                >
                    <DataCell>
                        <DataLabel>Name</DataLabel>
                        <DataText>
                            {{ getFullName(patient?.last_name!, patient?.first_name!, patient?.middle_name!) }}
                        </DataText>
                    </DataCell>
                    <DataCell v-if="!hasAnyRole(['doctor', 'laboratory_staff'])">
                        <DataLabel>Status</DataLabel>
                        <DataText
                            :class="{
                                'text-primary':
                                    appointment?.status === 'approved' || appointment?.status === 'completed',
                                'text-destructive':
                                    appointment?.status === 'rejected' || appointment?.status === 'cancelled',
                                'text-warning': appointment?.status === 'pending',
                            }"
                        >
                            {{ appointment?.status }}
                        </DataText>
                    </DataCell>
                </DataCard>

                <DataCard title="Service Type">
                    <DataText>
                        {{ ALL_SERVICES.find((service) => service.value === appointment?.type)?.label }}
                    </DataText>
                </DataCard>

                <DataCard title="Scheduled Date">
                    <DataText>
                        {{ appointment?.scheduled_at.formatted_date }}
                    </DataText>
                </DataCard>

                <DataCard title="Complaints / Notes">
                    <DataText>
                        {{ appointment?.complaints ?? 'N/A' }}
                    </DataText>
                </DataCard>
            </div>

            <DialogFooter>
                <template v-if="hasAnyRole(['doctor', 'laboratory_staff']) && appointment?.status === 'approved'">
                    <Button
                        @click="
                            () => {
                                setFieldValue('status', 'no_show');
                                noShowAppointment();
                            }
                        "
                        variant="destructive"
                        :disabled="inertiaForm.processing"
                        class="flex items-center gap-2"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing && values.status === 'no_show'"
                            class="h-4 w-4 animate-spin"
                        />
                        Did not show
                    </Button>
                </template>

                <template v-if="hasRole('admin') && appointment?.status === 'pending'">
                    <Button
                        v-if="hasPermissionTo('appointments:approve')"
                        @click="
                            () => {
                                setFieldValue('status', 'approved');
                                approveAppointment();
                            }
                        "
                        :disabled="inertiaForm.processing"
                        class="flex items-center gap-2"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing && values.status === 'approved'"
                            class="h-4 w-4 animate-spin"
                        />
                        Approve
                    </Button>

                    <Button
                        v-if="hasPermissionTo('appointments:reject')"
                        @click="
                            () => {
                                setFieldValue('status', 'rejected');
                                rejectAppointment();
                            }
                        "
                        variant="destructive"
                        :disabled="inertiaForm.processing"
                        class="flex items-center gap-2"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing && values.status === 'rejected'"
                            class="h-4 w-4 animate-spin"
                        />
                        Reject
                    </Button>
                </template>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>
