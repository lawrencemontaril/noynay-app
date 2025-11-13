<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import { Appointment } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { watch } from 'vue';
import * as z from 'zod';
import InputError from './InputError.vue';

const props = defineProps<{
    open: boolean;
    appointment: Appointment | null;
}>();
const emit = defineEmits(['update:open']);

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    appointment_id: props.appointment?.id,
    description: '',
    quantity: 1,
});

const formSchema = toTypedSchema(
    z.object({
        appointment_id: z.number({ required_error: 'Appointment is required.' }),
        description: z.string().min(1, 'Description is required'),
        quantity: z.coerce.number().int().min(1, 'Quantity must be at least 1'),
    }),
);

const { handleSubmit, setErrors, resetForm, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
});

const createProcedure = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.post(route('admin.procedures.store'), {
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

        if (props.appointment?.id) {
            setFieldValue('appointment_id', props.appointment.id);
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
                <DialogTitle>Create a procedure</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="createProcedure">
                <InputError
                    :message="inertiaForm.errors.appointment_id"
                    class="mb-4"
                />

                <!-- Description -->
                <FormField
                    name="description"
                    v-slot="{ componentField }"
                >
                    <FormItem class="flex-1">
                        <FormLabel required>Description</FormLabel>

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
                    name="quantity"
                    v-slot="{ componentField }"
                >
                    <FormItem>
                        <FormLabel required>Quantity</FormLabel>

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

                <DialogFooter>
                    <Button
                        type="submit"
                        :disabled="inertiaForm.processing"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Create procedure
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
