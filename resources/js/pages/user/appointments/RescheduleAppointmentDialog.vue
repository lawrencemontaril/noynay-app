<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import FormLabel from '@/components/ui/form/FormLabel.vue';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import { Appointment } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { CalendarIcon, LoaderCircle } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, watch } from 'vue';
import * as z from 'zod';

const props = defineProps<{
    appointment: Appointment;
    open: boolean;
}>();
const emit = defineEmits(['update:open']);

const closeDialog = () => {
    emit('update:open', false);
};

const inertiaForm = useInertiaForm({
    complaints: null as string | null,
    scheduled_at: '',
    status: 'pending',
});

const formSchema = toTypedSchema(
    z.object({
        complaints: z.string().nullable(),
        scheduled_date: z.string({ required_error: 'Appointment date is required.' }),
        scheduled_time: z.string({ required_error: 'Appointment time is required.' }),
    }),
);

const { handleSubmit, setErrors, setFieldValue, values, resetForm, setValues } = useVeeForm({
    validationSchema: formSchema,
});

watch(
    () => props.open,
    () => {
        setValues({
            complaints: props.appointment.complaints,
            scheduled_date: props.appointment.scheduled_at.date_time.split(' ')[0],
            scheduled_time: props.appointment.scheduled_at.date_time.split(' ')[1],
        });
    },
);

const rescheduleAppointment = handleSubmit((validatedValues) => {
    const scheduled_at = new Date(`${validatedValues.scheduled_date}T${validatedValues.scheduled_time}`).toISOString();
    inertiaForm.scheduled_at = scheduled_at;
    inertiaForm.complaints = validatedValues.complaints;

    inertiaForm.patch(route('appointments.reschedule', props.appointment?.id), {
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

function generateAvailableTimes(): { label: string; value: string }[] {
    const times: { label: string; value: string }[] = [];

    for (let hour = 0; hour < 24; hour++) {
        const ampm = hour < 12 ? 'AM' : 'PM';
        const displayHour = hour % 12 === 0 ? 12 : hour % 12;
        const label = `${displayHour}:00 ${ampm}`;
        const value = `${hour.toString().padStart(2, '0')}:00:00`;

        times.push({ label, value });
    }

    return times;
}

const availableTimes = generateAvailableTimes();

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
                <DialogTitle>Reschedule Appointment #{{ appointment.id }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="rescheduleAppointment">
                <InputError
                    :message="inertiaForm.errors.scheduled_at"
                    class="mb-4"
                />

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
                                                :class="
                                                    cn(
                                                        'w-full border-input text-start font-normal',
                                                        !scheduled_date && 'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <span>{{
                                                    scheduled_date
                                                        ? formatDate.format(toDate(scheduled_date))
                                                        : 'Select appointment date'
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
                                        <SelectItem
                                            v-for="time in availableTimes"
                                            :value="time.value"
                                            :key="time.value"
                                        >
                                            {{ time.label }}
                                        </SelectItem>
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
                        <FormLabel> Reason for appointment / Complaints </FormLabel>

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
