<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import { Dialog, DialogDescription, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import FormLabel from '@/components/ui/form/FormLabel.vue';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useFormatters } from '@/composables/useFormatters';
import { cn } from '@/lib/utils';
import { Appointment, Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { CalendarIcon, LoaderCircle } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, watch } from 'vue';
import * as z from 'zod';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    appointment: Appointment | null;
}>();
const emit = defineEmits(['update:open']);

const { toJsDate } = useFormatters();

const closeDialog = () => {
    emit('update:open', false);
};

const services = [
    {
        label: 'Consultation',
        value: 'consultation',
    },
    { label: 'Family Planning Counseling', value: 'family_planning_counseling' },
    { label: 'Natural Methods (Rhythm), Pills, Depotrust', value: 'natural_methods' },
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
    { label: 'Pregnancy Test', value: 'pregnancy_test' },
    { label: 'Papsmear', value: 'papsmear' },
    { label: 'Complete Blood Count', value: 'cbc' },
    { label: 'Urinalysis', value: 'urinalysis' },
    { label: 'Fecalysis', value: 'fecalysis' },
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
];

const inertiaForm = useInertiaForm({
    complaints: null as string | null | undefined,
    type: '',
    scheduled_at: '',
    status: '',
});

const formSchema = toTypedSchema(
    z.object({
        type: z.string({ required_error: 'Service Type is required.' }),
        complaints: z.string().nullable(),
        scheduled_date: z.string({ required_error: 'Appointment date is required.' }),
        scheduled_time: z.string({ required_error: 'Appointment time is required.' }),
        status: z.string({ required_error: 'Status is required.' }),
    }),
);

const { handleSubmit, setErrors, setFieldValue, values, resetForm, setValues } = useVeeForm({
    validationSchema: formSchema,
});

watch(
    () => props.open,
    () => {
        setValues({
            type: props.appointment?.type,
            complaints: props.appointment?.complaints,
            scheduled_date: props.appointment?.scheduled_at.date_time.split(' ')[0],
            scheduled_time: props.appointment?.scheduled_at.date_time.split(' ')[1],
            status: props.appointment?.status,
        });
    },
);

const updateAppointment = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);
    inertiaForm.scheduled_at = toJsDate(values.scheduled_date!, values.scheduled_time!).toISOString();

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

const formatDate = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const scheduled_date = computed({
    get: () => (values.scheduled_date ? parseDate(values.scheduled_date) : undefined),
    set: (val) => val,
});
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Edit Appointment #{{ appointment?.id }}</DialogTitle>
                <DialogDescription>Update this patient's appointment details.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updateAppointment">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="mb-4 grid grid-cols-1 gap-2">
                        <label class="border-b pb-1 text-xs font-semibold uppercase">Patient Name</label>
                        <p class="text-sm">
                            {{ patient?.last_name }}, {{ patient?.first_name }}
                            {{ patient?.middle_name ? `${patient?.middle_name[0]}.` : '' }}
                        </p>
                    </div>

                    <FormField
                        v-slot="{ componentField }"
                        name="status"
                    >
                        <FormItem>
                            <FormLabel required>Status</FormLabel>

                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                </FormControl>

                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="approved">Approved</SelectItem>
                                        <SelectItem value="rejected">Rejected</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <FormField
                    v-slot="{ componentField }"
                    name="type"
                >
                    <FormItem>
                        <FormLabel required>Service Type</FormLabel>

                        <Select v-bind="componentField">
                            <FormControl>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a service type." />
                                </SelectTrigger>
                            </FormControl>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem
                                        v-for="service in services"
                                        :value="service.value"
                                        :key="service.value"
                                        @select="
                                            () => {
                                                setFieldValue('type', service.value);
                                            }
                                        "
                                    >
                                        {{ service.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <FormField name="scheduled_date">
                        <FormItem>
                            <FormLabel required> Date </FormLabel>

                            <Popover>
                                <PopoverAnchor>
                                    <PopoverTrigger as-child>
                                        <FormControl>
                                            <Button
                                                variant="outline"
                                                :class="cn('w-full border-input text-start font-normal', !scheduled_date && 'text-muted-foreground')"
                                            >
                                                <span>{{
                                                    scheduled_date ? formatDate.format(toDate(scheduled_date)) : 'Select appointment date'
                                                }}</span>
                                                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
                                            </Button>
                                            <input hidden />
                                        </FormControl>
                                    </PopoverTrigger>
                                </PopoverAnchor>

                                <PopoverContent class="w-auto p-0">
                                    <Calendar
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
                                </PopoverContent>
                            </Popover>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="scheduled_time"
                    >
                        <FormItem>
                            <FormLabel required>Time</FormLabel>

                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select appointment time" />
                                    </SelectTrigger>
                                </FormControl>

                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="09:00:00">9:00 AM</SelectItem>
                                        <SelectItem value="11:00:00">11:00 AM</SelectItem>
                                        <SelectItem value="12:00:00">12:00 PM</SelectItem>
                                        <SelectItem value="13:00:00">1:00 PM</SelectItem>
                                        <SelectItem value="14:00:00">2:00 PM</SelectItem>
                                        <SelectItem value="15:00:00">3:00 PM</SelectItem>
                                        <SelectItem value="16:00:00">4:00 PM</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </FormItem>
                    </FormField>
                </div>

                <FormField
                    v-slot="{ componentField }"
                    name="complaints"
                >
                    <FormItem>
                        <FormLabel> Complaints / Notes </FormLabel>

                        <FormControl>
                            <Textarea v-bind="componentField" />
                        </FormControl>

                        <FormMessage />
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
                        Update appointment
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
