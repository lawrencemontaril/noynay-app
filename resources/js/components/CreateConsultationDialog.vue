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
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useFormatters } from '@/composables/useFormatters';
import { Appointment, Patient } from '@/types';
import { CONSULTATION_TYPES } from '@/types/constants';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, watch } from 'vue';
import * as z from 'zod';
import DataCard from './DataCard.vue';
import InputError from './InputError.vue';

const props = withDefaults(
    defineProps<{
        open?: boolean;
        appointment?: Appointment | null;
        patient: Patient;
        isTypeEditable?: boolean;
    }>(),
    {
        isTypeEditable: false,
    },
);
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    appointment_id: props.appointment?.id,
    type: '',
    chief_complaints: '',
    assessment: '',
    plan: '',
    systolic: null as number | null,
    diastolic: null as number | null,
    heart_rate: null as number | null,
    respiratory_rate: null as number | null,
    weight_kg: null as number | null,
    height_cm: null as number | null,
    temperature_c: null as number | null,
    oxygen_saturation: null as number | null,
});

const formSchema = toTypedSchema(
    z
        .object({
            appointment_id: z.number({ required_error: 'Appointment is required.' }),
            type: z.string({ required_error: 'Type field is required.' }),
            chief_complaints: z.string({ required_error: 'Chief Complaints field is required.' }),
            assessment: z.string({ required_error: 'Assessment field is required.' }),
            plan: z.string({ required_error: 'Plan field is required.' }),
            systolic: z.number().min(70, 'Systolic too low').max(250, 'Systolic too high').nullable(),
            diastolic: z.number().min(40, 'Diastolic too low').max(150, 'Diastolic too high').nullable(),
            heart_rate: z.number().min(30, 'Heart rate too low.').max(220, 'Heart rate too high.').nullable(),
            respiratory_rate: z
                .number()
                .min(8, 'Respiratory rate too low.')
                .max(40, 'Respiratory rate too high.')
                .nullable(),
            weight_kg: z.coerce.number().max(999, 'Unrealistic weight.').nullable(),
            height_cm: z.coerce.number().max(300, 'Unrealistic height.').nullable(),
            temperature_c: z.coerce.number().max(50, 'Unrealistic temperature.').nullable(),
            oxygen_saturation: z.coerce
                .number()
                .min(70, 'Oxygen saturation must be greater than or equal to 70.')
                .max(100, 'Oxygen saturation must be less than or equal to 100.')
                .nullable(),
        })
        .refine(
            (data) => {
                if (data.systolic !== null && data.diastolic !== null) {
                    return data.systolic > data.diastolic;
                }
                return true;
            },
            {
                message: 'Systolic must be greater than diastolic',
                path: ['systolic'],
            },
        ),
);

const { handleSubmit, setErrors, values, resetForm, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
    initialValues: inertiaForm,
});

const createConsultation = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.post(route('admin.consultations.store'), {
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

        if (props.appointment) {
            setFieldValue('appointment_id', props.appointment?.id);
            setFieldValue('type', props.appointment?.type);
        }
    },
);

const bmi = computed(() => {
    if (!values.weight_kg || !values.height_cm) return '';
    const h = values.height_cm / 100;
    return +(values.weight_kg / (h * h)).toFixed(1);
});

const bmiCategory = computed(() => {
    if (!bmi.value) return '';
    if (bmi.value < 18.5) return 'Underweight';
    if (bmi.value < 25) return 'Normal';
    if (bmi.value < 30) return 'Overweight';
    if (bmi.value < 35) return 'Obese (Class I)';
    if (bmi.value < 40) return 'Obese (Class II)';
    return 'Obese (Class III)';
});
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Create consultation</DialogTitle>
                <DialogDescription>Fill up patient details and vitals</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="createConsultation">
                <InputError :message="inertiaForm.errors.appointment_id" />

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

                <FormField
                    v-slot="{ componentField }"
                    name="type"
                >
                    <FormItem>
                        <FormLabel :required="isTypeEditable"> Type </FormLabel>

                        <Select
                            v-bind="componentField"
                            :disabled="!isTypeEditable"
                        >
                            <FormControl>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                            </FormControl>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem
                                        v-for="type in CONSULTATION_TYPES"
                                        :value="type.value"
                                        :key="type.value"
                                    >
                                        {{ type.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="chief_complaints"
                >
                    <FormItem>
                        <FormLabel required>Patient Complaints</FormLabel>

                        <FormControl>
                            <Textarea
                                v-bind="componentField"
                                placeholder="What are the patient's complaints?"
                            />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="assessment"
                >
                    <FormItem>
                        <FormLabel required>Assessment</FormLabel>

                        <FormControl>
                            <Textarea v-bind="componentField" />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="plan"
                >
                    <FormItem>
                        <FormLabel required>Plan</FormLabel>

                        <FormControl>
                            <Textarea v-bind="componentField" />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <fieldset class="mb-4 grid grid-cols-2 gap-4 rounded-md border border-input p-4 pb-0 shadow-xs">
                    <legend class="text-xs font-semibold uppercase">Blood Pressure</legend>

                    <FormField
                        v-slot="{ componentField }"
                        name="systolic"
                    >
                        <FormItem>
                            <FormLabel>Systolic</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="diastolic"
                    >
                        <FormItem>
                            <FormLabel>Diastolic</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </fieldset>

                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        v-slot="{ componentField }"
                        name="heart_rate"
                    >
                        <FormItem>
                            <FormLabel>Heart Rate</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="respiratory_rate"
                    >
                        <FormItem>
                            <FormLabel>Respiratory Rate</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        v-slot="{ componentField }"
                        name="weight_kg"
                    >
                        <FormItem>
                            <FormLabel>Weight (kg)</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                    step="any"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="height_cm"
                    >
                        <FormItem>
                            <FormLabel>Height (cm)</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                    step="any"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <FormField name="bmi">
                    <FormItem>
                        <FormLabel>Body Mass Index (BMI)</FormLabel>

                        <FormControl>
                            <Input
                                :model-value="`${bmi ? `${bmi} ; ${bmiCategory}` : ''}`"
                                class="rounded-none border-b-2 border-none disabled:opacity-100"
                                disabled
                            />
                        </FormControl>
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        v-slot="{ componentField }"
                        name="temperature_c"
                    >
                        <FormItem>
                            <FormLabel>Temperature (&deg;C)</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                    step="any"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="oxygen_saturation"
                    >
                        <FormItem>
                            <FormLabel>Oxygen Saturation (%)</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    type="number"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <DialogFooter>
                    <Button
                        type="submit"
                        :disabled="inertiaForm.processing"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Create consultation
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
