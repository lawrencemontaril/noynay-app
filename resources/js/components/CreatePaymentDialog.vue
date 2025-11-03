<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import { useFormatters } from '@/composables/useFormatters';
import { Invoice, Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { watch } from 'vue';
import * as z from 'zod';
import DataCard from './DataCard.vue';
import InputError from './InputError.vue';

const props = defineProps<{
    open: boolean;
    invoice: Invoice | null;
    patient: Patient;
}>();
const emit = defineEmits(['update:open']);

const { getFullName, formatCurrency } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    invoice_id: props.invoice?.id,
    amount: 0,
});

const formSchema = toTypedSchema(
    z.object({
        invoice_id: z.number({ required_error: 'Invoice is required.' }),
        amount: z.number({ required_error: 'Amount field is required.' }),
    }),
);

const { handleSubmit, setErrors, resetForm, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
});

const createPayment = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.post(route('admin.payments.store'), {
        onError: (serverErrors) => setErrors(serverErrors),
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

watch(
    () => props.open,
    () => {
        inertiaForm.reset();
        inertiaForm.clearErrors();
        resetForm();

        if (props.invoice?.id) {
            setFieldValue('invoice_id', props.invoice.id);
        }
    },
);
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Payment</DialogTitle>
                <DialogDescription>Fill up patient payment details.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="createPayment">
                <InputError
                    :message="inertiaForm.errors.invoice_id"
                    class="mb-4"
                />

                <DataCard
                    title="Patient Information"
                    :columns="2"
                >
                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Name</label>
                        <p class="text-sm font-semibold">
                            {{ getFullName(patient.last_name, patient.first_name, patient.middle_name) }}
                        </p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Amount Payable</label>
                        <p class="text-sm">
                            {{ formatCurrency(invoice?.balance ?? 0) }}
                        </p>
                    </div>
                </DataCard>

                <FormField
                    name="amount"
                    v-slot="{ componentField }"
                >
                    <FormItem>
                        <FormLabel required>Amount</FormLabel>

                        <FormControl>
                            <Input
                                v-bind="componentField"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded border p-2"
                            />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <DialogFooter>
                    <Button
                        type="submit"
                        :disabled="inertiaForm.processing"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Create payment
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
