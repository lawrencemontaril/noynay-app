<script setup lang="ts">
import Container from '@/components/Container.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { useFormatters } from '@/composables/useFormatters';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { Check, Circle, Dot } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, ref } from 'vue';
import * as z from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointments',
        href: route('appointments.index'),
    },
    {
        title: 'Create',
        href: route('appointments.create'),
    },
];

const { dateFormatter, timeFormatter, toJsDate } = useFormatters();

const services = {
    consultation: {
        label: 'Consultation',
        children: [{ label: 'Consultation', value: 'consultation' }],
    },
    family_planning_service: {
        label: 'Family Planning Services',
        children: [
            { label: 'Family Planning Counseling', value: 'family_planning_counseling' },
            { label: 'Natural Methods (Rhythm), Pills, Depotrust', value: 'natural_methods' },
        ],
    },
    integrative_and_wellness: {
        label: 'Integrative and Wellness Healthcare Services',
        children: [
            { label: 'Chelation Therapy', value: 'chelation_therapy' },
            { label: 'Magnetic Resonance Analysis', value: 'magnetic_resonance_analysis' },
            {
                label: 'Multifunctional High Potential Therapeutic Services',
                value: 'multifunctional_high_potential_therapeutic_services',
            },
            { label: 'Weight Loss Management', value: 'weight_loss_management' },
            {
                label: 'Psychosocial and Spiritual Counseling',
                value: 'psychosocial_and_spiritual_counseling',
            },
        ],
    },
    laboratory_services: {
        label: 'Laboratory Services',
        children: [
            { label: 'Pregnancy Test', value: 'pregnancy_test' },
            { label: 'Papsmear', value: 'papsmear' },
            { label: 'Complete Blood Count', value: 'cbc' },
            { label: 'Urinalysis', value: 'urinalysis' },
            { label: 'Fecalysis', value: 'fecalysis' },
        ],
    },
    maternal_and_child_health_services: {
        label: 'Maternal and Child Health Services',
        children: [
            { label: 'Pre-Natal and Post-Natal Check Up', value: 'pre_natal_and_post_natal' },
            { label: 'Normal Spontaneous Delivery', value: 'normal_spontaneous_delivery' },
            { label: 'Immunization - BCG, HEP. B Vaccines, etc.', value: 'immunization' },
            { label: 'Ear Piercing With Hypoallergenic Earrings', value: 'ear_pearcing' },
            { label: 'Nebulization With and Without Medication', value: 'nebulization' },
            { label: 'Foley Cathether Insertion', value: 'foley_catheter_insertion' },
            { label: 'Surgical Wound Dressing', value: 'surgical_wound_dressing' },
            { label: 'Cord Dressing', value: 'cord_dressing' },
            { label: 'Suture Removal', value: 'suture_removal' },
            {
                label: 'Issuance of Birth Certificate; Newborn Screening',
                value: 'issuance_of_bc_newborn_screening',
            },
        ],
    },
    medical_surgical_services: {
        label: 'Medical/Surgical Services',
        children: [
            { label: 'General OPD Consultation', value: 'general_opd_consultation' },
            {
                label: 'Medical / OPD / Pre-Employment Consultations',
                value: 'medical_opd_consultation',
            },
            { label: 'Minor Surgical Procedures', value: 'minor_surgical_procedures' },
            {
                label: 'Issuance of Medical Certificate',
                value: 'issuance_of_medical_certificate',
            },
            {
                label: 'Pedia / Adult Immunization / Vaccination Services',
                value: 'pedia_adult_vaccination_services',
            },
        ],
    },
} as const;

type ServiceKey = keyof typeof services;
const selectedService = ref<ServiceKey | ''>('');

const stepIndex = ref(1);
const steps = [
    {
        step: 1,
        title: 'Select a service',
        description: 'What do you need?',
    },
    {
        step: 2,
        title: 'Date & Time',
        description: 'When are you available?',
    },
    {
        step: 3,
        title: 'Confirmation',
        description: 'Ensure information is correct',
    },
];

const availableTimes = [
    {
        label: '9:00 AM',
        value: '09:00:00',
    },
    {
        label: '11:00 AM',
        value: '11:00:00',
    },
    {
        label: '12:00 PM',
        value: '12:00:00',
    },
    {
        label: '1:00 PM',
        value: '13:00:00',
    },
    {
        label: '2:00 PM',
        value: '14:00:00',
    },
    {
        label: '3:00 PM',
        value: '15:00:00',
    },
    {
        label: '4:00 PM',
        value: '16:00:00',
    },
];

const formSchema = [
    z.object({
        complaints: z.string().nullable().optional(),
        type: z.string({ required_error: 'Service field is required.' }),
    }),
    z.object({
        scheduled_date: z.string({ required_error: 'Date field is required.' }),
        scheduled_time: z.string({ required_error: 'Time field is required.' }),
    }),
    z.object({}),
];

type FormValues = {
    complaints?: string | null;
    type: string;
    scheduled_date: string;
    scheduled_time: string;
};

const { setErrors, setFieldValue, values, validate, meta, handleSubmit } = useVeeForm<FormValues>({
    validationSchema: computed(() => toTypedSchema(formSchema[stepIndex.value - 1])),
    keepValuesOnUnmount: true,
});

const inertiaForm = useInertiaForm({
    type: '',
    complaints: null as undefined | string | null,
    scheduled_at: '',
});

const createAppointment = handleSubmit(() => {
    validate();

    if (stepIndex.value === steps.length && meta.value.valid) {
        inertiaForm.type = values.type;
        inertiaForm.complaints = values.complaints;
        inertiaForm.scheduled_at = toJsDate(values.scheduled_date, values.scheduled_time).toISOString();

        inertiaForm.post(route('appointments.store'), {
            onError: (serverErrors) => {
                setErrors(serverErrors);
            },
        });
    }
});

const scheduled_date = computed({
    get: () => (values.scheduled_date ? parseDate(values.scheduled_date) : undefined),
    set: (val) => val,
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <Stepper
                v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }"
                v-model="stepIndex"
                class="block w-full"
            >
                <form @submit.prevent="createAppointment">
                    <div class="flex-start flex w-full gap-2">
                        <StepperItem
                            v-for="step in steps"
                            :key="step.step"
                            v-slot="{ state }"
                            class="relative flex w-full flex-col items-center justify-center"
                            :step="step.step"
                        >
                            <StepperSeparator
                                v-if="step.step !== steps[steps.length - 1].step"
                                class="absolute top-4 right-[calc(-50%+10px)] left-[calc(50%+20px)] block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                            />

                            <StepperTrigger as-child>
                                <Button
                                    :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                                    size="icon"
                                    class="shrink-0 rounded-full"
                                    :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                                    :disabled="state !== 'completed' && !meta.valid"
                                >
                                    <Check
                                        v-if="state === 'completed'"
                                        class="size-5"
                                    />
                                    <Circle v-if="state === 'active'" />
                                    <Dot v-if="state === 'inactive'" />
                                </Button>
                            </StepperTrigger>

                            <div class="mt-5 flex flex-col items-center text-center">
                                <StepperTitle
                                    :class="[state === 'active' && 'text-primary']"
                                    class="text-sm font-semibold transition lg:text-base"
                                >
                                    {{ step.title }}
                                </StepperTitle>
                                <StepperDescription
                                    :class="[state === 'active' && 'text-primary']"
                                    class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm"
                                >
                                    {{ step.description }}
                                </StepperDescription>
                            </div>
                        </StepperItem>
                    </div>

                    <div class="mt-4 flex flex-col rounded-md border p-3 shadow-xs">
                        <template v-if="stepIndex === 1">
                            <FormField name="type">
                                <FormItem>
                                    <FormLabel required>Service</FormLabel>

                                    <Select v-model="selectedService">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a service" />
                                        </SelectTrigger>

                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem
                                                    v-for="(service, key) in services"
                                                    :value="key"
                                                    :key="key"
                                                >
                                                    {{ service.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>

                                    <FormControl>
                                        <template v-if="selectedService">
                                            <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                                                <Button
                                                    v-for="child in services[selectedService]?.children"
                                                    :key="child.value"
                                                    :variant="values.type === child.value ? 'default' : 'outline'"
                                                    @click="setFieldValue('type', child.value)"
                                                    class="flex h-full w-full flex-col justify-start gap-0 p-1"
                                                >
                                                    <img
                                                        :src="`/images/services/${child.value}.jpg`"
                                                        class="block w-auto"
                                                    />
                                                    <p class="p-2 text-wrap">{{ child.label }}</p>
                                                </Button>
                                            </div>
                                        </template>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField
                                v-slot="{ componentField }"
                                name="complaints"
                            >
                                <FormItem>
                                    <FormLabel>Complaints / Notes</FormLabel>

                                    <FormControl>
                                        <Textarea v-bind="componentField" />
                                    </FormControl>

                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </template>

                        <template v-if="stepIndex === 2">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <FormField name="scheduled_date">
                                    <FormItem>
                                        <FormLabel required>Date</FormLabel>
                                        <FormControl class="flex flex-col items-center justify-center">
                                            <Calendar
                                                button-size="icon12"
                                                class="p-0"
                                                :model-value="scheduled_date"
                                                calendar-label="Appointment date"
                                                initial-focus
                                                :min-value="today(getLocalTimeZone())"
                                                :max-value="today(getLocalTimeZone()).add({ years: 1 })"
                                                @update:model-value="
                                                    (v) => {
                                                        if (v) {
                                                            setFieldValue('scheduled_date', v.toString());
                                                        }
                                                    }
                                                "
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField name="scheduled_time">
                                    <FormItem>
                                        <FormLabel required>Time</FormLabel>
                                        <FormControl>
                                            <div class="flex flex-wrap gap-4">
                                                <Button
                                                    v-for="time in availableTimes"
                                                    class="w-fit"
                                                    :variant="values.scheduled_time === time.value ? 'default' : 'outline'"
                                                    @click="setFieldValue('scheduled_time', time.value)"
                                                    :key="time.value"
                                                >
                                                    {{ time.label }}
                                                </Button>
                                            </div>
                                        </FormControl>

                                        <FormMessage />
                                    </FormItem>
                                </FormField>
                            </div>
                        </template>

                        <template v-if="stepIndex === 3">
                            <h1 class="mt-2 mb-6 text-center text-xl font-semibold">Confirm appointment details</h1>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="grid grid-cols-1 gap-2">
                                    <label class="border-b pb-1 text-xs font-semibold uppercase">Service</label>
                                    <p
                                        v-if="selectedService"
                                        class="text-sm"
                                    >
                                        {{ services[selectedService]?.label }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-2">
                                    <label class="border-b pb-1 text-xs font-semibold uppercase">Service Type</label>
                                    <p
                                        v-if="selectedService"
                                        class="text-sm"
                                    >
                                        {{ services[selectedService]?.children.find((s) => s.value === values.type)?.label }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-2">
                                    <label class="border-b pb-1 text-xs font-semibold uppercase">Date</label>
                                    <p class="text-sm">
                                        {{ dateFormatter.format(toJsDate(values.scheduled_date, values.scheduled_time)) }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-2">
                                    <label class="border-b pb-1 text-xs font-semibold uppercase">Time</label>
                                    <p class="text-sm">
                                        {{ timeFormatter.format(toJsDate(values.scheduled_date, values.scheduled_time)) }}
                                    </p>
                                </div>
                            </div>

                            <InputError
                                class="mt-4"
                                :message="inertiaForm.errors.scheduled_at"
                            />
                        </template>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <Button
                            :disabled="isPrevDisabled"
                            variant="outline"
                            @click="prevStep()"
                        >
                            Back
                        </Button>
                        <div class="flex items-center gap-3">
                            <Button
                                v-if="stepIndex !== 3"
                                :type="meta.valid ? 'button' : 'submit'"
                                :disabled="isNextDisabled"
                                @click="meta.valid && nextStep()"
                            >
                                Next
                            </Button>
                            <Button
                                v-if="stepIndex === 3"
                                type="submit"
                            >
                                Submit
                            </Button>
                        </div>
                    </div>
                </form>
            </Stepper>
        </Container>
    </AppLayout>
</template>
