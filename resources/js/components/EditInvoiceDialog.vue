<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import { useFormatters } from '@/composables/useFormatters';
import { Appointment, Invoice, Patient } from '@/types';
import { ALL_SERVICES } from '@/types/constants';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle, Plus, Trash } from 'lucide-vue-next';
import { useFieldArray, useForm as useVeeForm } from 'vee-validate';
import { watch } from 'vue';
import * as z from 'zod';
import DataCard from './DataCard.vue';
import InputError from './InputError.vue';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    appointment?: Appointment | null;
    invoice: Invoice | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    appointment_id: 0,
    items: [],
});

const itemSchema = z.object({
    id: z.number().optional(),
    description: z.string().min(1, 'Description is required'),
    quantity: z.coerce.number().int().min(1, 'Quantity must be at least 1'),
    unit_price: z.coerce.number().min(0, 'Price must be positive'),
});

const formSchema = toTypedSchema(
    z.object({
        appointment_id: z.number({ required_error: 'Appointment is required.' }),
        items: z
            .array(itemSchema)
            .min(1, 'Add at least one item')
            .default([{ description: '', quantity: 1, unit_price: 0 }]),
    }),
);

const { handleSubmit, setErrors, resetForm, setValues } = useVeeForm({
    validationSchema: formSchema,
});

watch(
    () => props.open,
    () => {
        setValues({
            appointment_id: props.invoice?.appointment_id,
            items: props.invoice?.invoice_items!.map((item) => ({
                id: item.id,
                description: item.description,
                quantity: item.quantity,
                unit_price: item.unit_price,
            })),
        });
    },
    { immediate: true },
);

const updateInvoice = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.patch(route('admin.invoices.update', props.invoice?.id), {
        onError: (serverErrors) => setErrors(serverErrors),
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

type Item = z.infer<typeof itemSchema>;
const { fields: itemFields, push, remove } = useFieldArray<Item>('items');

const addItem = () => {
    push({ description: '', quantity: 1, unit_price: 0 });
};
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Edit Invoice #{{ invoice?.id }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="updateInvoice">
                <InputError
                    :message="inertiaForm.errors.appointment_id"
                    class="mb-4"
                />

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
                        <label class="text-xs font-medium text-muted-foreground">Service</label>
                        <p class="text-sm">
                            {{ ALL_SERVICES.find((service) => service.value === appointment?.type)?.label }}
                        </p>
                    </div>
                </DataCard>

                <FormField name="items">
                    <FormItem>
                        <FormLabel required>Invoice Items</FormLabel>

                        <div
                            v-for="(field, index) in itemFields"
                            :key="field.key"
                            class="relative mb-4 flex items-start gap-2 rounded-lg border p-3 pb-0 shadow-xs"
                        >
                            <FormField
                                :name="`items.${index}.description`"
                                v-slot="{ componentField }"
                            >
                                <FormItem class="flex-1">
                                    <FormLabel>Description</FormLabel>
                                    <FormControl>
                                        <Input
                                            v-bind="componentField"
                                            type="text"
                                            class="w-full rounded border p-2"
                                            placeholder="Item description"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField
                                :name="`items.${index}.quantity`"
                                v-slot="{ componentField }"
                            >
                                <FormItem class="w-24">
                                    <FormLabel>Qty</FormLabel>
                                    <FormControl>
                                        <Input
                                            v-bind="componentField"
                                            type="number"
                                            min="1"
                                            class="w-full rounded border p-2"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField
                                :name="`items.${index}.unit_price`"
                                v-slot="{ componentField }"
                            >
                                <FormItem class="w-32">
                                    <FormLabel>Unit Price</FormLabel>
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

                            <Button
                                type="button"
                                variant="destructive"
                                size="icon"
                                class="absolute -top-2 right-2"
                                @click="remove(index)"
                                v-if="itemFields.length > 1"
                            >
                                <Trash />
                            </Button>
                        </div>

                        <FormMessage />

                        <Button
                            type="button"
                            variant="outline"
                            @click="addItem"
                        >
                            <Plus /> Add Item
                        </Button>
                    </FormItem>
                </FormField>

                <DialogFooter>
                    <Button
                        type="submit"
                        variant="warning"
                        :disabled="inertiaForm.processing"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Update invoice
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
