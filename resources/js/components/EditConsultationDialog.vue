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
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { useFormatters } from '@/composables/useFormatters';
import { Appointment, Consultation, Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, watch } from 'vue';
import * as z from 'zod';
import DataCard from './DataCard.vue';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    appointment?: Appointment | null;
    consultation: Consultation | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    appointment_id: 0,
    chief_complaints: '',
    systolic: 0,
    diastolic: 0,
    heart_rate: 0,
    respiratory_rate: 0,
    weight_kg: 0 as number | null | undefined,
    height_cm: 0 as number | null | undefined,
    temperature_c: 0,
    oxygen_saturation: 0 as number | null | undefined,
    assessment: '' as string | null | undefined,
    plan: '' as string | null | undefined,
});

const formSchema = toTypedSchema(
    z
        .object({
            appointment_id: z.number({ required_error: 'Appointment is required.' }),
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

const { handleSubmit, setErrors, values, resetForm, setValues } = useVeeForm({
    validationSchema: formSchema,
});

watch(
    () => props.open,
    () => {
        setValues({
            appointment_id: props.consultation?.appointment_id,
            chief_complaints: props.consultation?.chief_complaints,
            systolic: props.consultation?.systolic,
            diastolic: props.consultation?.diastolic,
            heart_rate: props.consultation?.heart_rate,
            respiratory_rate: props.consultation?.respiratory_rate,
            weight_kg: props.consultation?.weight_kg,
            height_cm: props.consultation?.height_cm,
            temperature_c: props.consultation?.temperature_c,
            oxygen_saturation: props.consultation?.oxygen_saturation,
            assessment: props.consultation?.assessment,
            plan: props.consultation?.plan,
        });
    },
    { immediate: true },
);

const updateConsultation = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.patch(route('admin.consultations.update', props.consultation?.id), {
        onError: (serverErrors) => setErrors(serverErrors),
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

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
                <DialogTitle>Edit Consultation #{{ consultation?.id }}</DialogTitle>
                <DialogDescription>Update patient details and vitals</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updateConsultation">
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
                    name="chief_complaints"
                >
                    <FormItem>
                        <FormLabel>Chief Complaints</FormLabel>

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
                        variant="warning"
                        :disabled="inertiaForm.processing"
                    >
                        <LoaderCircle
                            v-if="inertiaForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Update consultation
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
