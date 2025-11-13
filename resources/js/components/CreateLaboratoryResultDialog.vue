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
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFormatters } from '@/composables/useFormatters';
import { Appointment, Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { watch } from 'vue';
import * as z from 'zod';
import DataCard from './DataCard.vue';
import DataText from './DataText.vue';
import InputError from './InputError.vue';
import Input from './ui/input/Input.vue';
import Textarea from './ui/textarea/Textarea.vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        appointment: Appointment;
        patient: Patient;
        isTypeEditable?: boolean;
    }>(),
    {
        isTypeEditable: false,
    },
);
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

const inertiaForm = useInertiaForm({
    appointment_id: props.appointment.id,
    description: '',
    type: props.appointment.type,
    results_file: null as File | null,
});

const formSchema = toTypedSchema(
    z.object({
        appointment_id: z.number({ required_error: 'Appointment is required.' }),
        description: z.string({ required_error: 'Description is required.' }),
        type: z.string({ required_error: 'Test Type is required.' }),
        results_file: z
            .instanceof(File, { message: 'A valid file is required.' })
            .refine((file) => file.type === 'application/pdf', {
                message: 'Only PDF files are allowed.',
            })
            .refine((file) => file.size <= 12 * 1024 * 1024, {
                message: 'File size must be less than 12MB.',
            })
            .nullable()
            .optional(),
    }),
);

const { handleSubmit, setErrors, resetForm, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
});

const createLaboratoryResult = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.post(route('admin.laboratory_results.store'), {
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
        if (props.appointment) {
            setFieldValue('appointment_id', props.appointment.id);
            setFieldValue('type', props.appointment.type);
        }
    },
);

function closeDialog() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent class="w-[768px]">
            <DialogHeader>
                <DialogTitle>Create a laboratory result</DialogTitle>
                <DialogDescription>Fill up patient laboratory result details.</DialogDescription>
            </DialogHeader>

            <form
                @submit.prevent="createLaboratoryResult"
                enctype="multipart/form-data"
            >
                <InputError :message="inertiaForm.errors.appointment_id" />

                <DataCard title="Patient Name">
                    <DataText>
                        {{ getFullName(patient.last_name, patient.first_name, patient.middle_name) }}
                    </DataText>
                </DataCard>

                <FormField
                    v-slot="{ componentField }"
                    name="type"
                >
                    <FormItem>
                        <FormLabel :required="isTypeEditable"> Test Type </FormLabel>

                        <Select
                            v-bind="componentField"
                            :disabled="!isTypeEditable"
                        >
                            <FormControl>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select test type" />
                                </SelectTrigger>
                            </FormControl>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="pregnancy_test"> Pregnancy Test </SelectItem>
                                    <SelectItem value="papsmear"> Papsmear </SelectItem>
                                    <SelectItem value="cbc"> Complete Blood Count </SelectItem>
                                    <SelectItem value="urinalysis"> Urinalysis </SelectItem>
                                    <SelectItem value="fecalysis"> Fecalysis </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="description"
                >
                    <FormItem>
                        <FormLabel required> Description </FormLabel>

                        <FormControl>
                            <Textarea v-bind="componentField" />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField name="results_file">
                    <FormItem>
                        <FormLabel> Results File </FormLabel>

                        <FormControl>
                            <Input
                                type="file"
                                accept="application/pdf"
                                @change="
                                    (e: Event) => {
                                        const file = (e.target as HTMLInputElement).files?.[0] ?? null;
                                        setFieldValue('results_file', file);
                                        inertiaForm.results_file = file; // keep inertia in sync
                                    }
                                "
                            />
                        </FormControl>

                        <FormDescription> The file must be in PDF format, 12MB max. </FormDescription>

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
                        Create laboratory result
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
