<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import { Dialog, DialogDescription, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import Input from '@/components/ui/input/Input.vue';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFormatters } from '@/composables/useFormatters';
import { cn } from '@/lib/utils';
import { Patient, User } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { CalendarDate, DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { CalendarIcon, Check, ChevronsUpDown, LoaderCircle } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, ref, watch } from 'vue';
import * as z from 'zod';
import Calendar from './ui/calendar/Calendar.vue';
import Textarea from './ui/textarea/Textarea.vue';

const props = defineProps<{
    open: boolean;
    patient: Patient | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    user_id: props.patient?.user_id,
    first_name: props.patient?.first_name,
    last_name: props.patient?.last_name,
    middle_name: props.patient?.middle_name as string | null | undefined,
    gender: props.patient?.gender,
    civil_status: props.patient?.civil_status,
    birthdate: props.patient?.birthdate.date_time,
    contact_number: props.patient?.contact_number,
    address: props.patient?.address,
});

const formSchema = toTypedSchema(
    z.object({
        user_id: z.number().or(z.null()).optional(),
        first_name: z.string({ required_error: 'First Name is required.' }).max(80, 'Exceeded maximum character of 80.'),
        last_name: z.string({ required_error: 'Last Name is required.' }).max(80, 'Exceeded maximum character of 80.'),
        middle_name: z.string().max(80, 'Exceeded maximum character of 80.').or(z.null()).optional(),
        gender: z.enum(['male', 'female']),
        civil_status: z.enum(['single', 'married', 'widowed', 'divorced', 'separated']),
        birthdate: z.string({ required_error: 'Birthdate is required.' }),
        contact_number: z.string({ required_error: 'Contact Number is required.' }).max(24, 'Exceeded maximum character of 24.'),
        address: z.string({ required_error: 'Address is required.' }),
    }),
);

const { handleSubmit, values, setErrors, resetForm, setValues, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
    initialValues: inertiaForm,
});

const updatePatient = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.patch(route('admin.patients.update', props.patient?.id), {
        onError: (serverErrors) => setErrors(serverErrors),
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

const users = ref<User[]>();
const selectedUser = ref<User | null>(props.patient?.user ?? null);

watch(
    () => props.open,
    () => {
        inertiaForm.reset();
        inertiaForm.clearErrors();
        resetForm();

        if (props.patient) {
            setValues({
                first_name: props.patient?.first_name,
                last_name: props.patient?.last_name,
                middle_name: props.patient?.middle_name,
                gender: props.patient?.gender,
                civil_status: props.patient?.civil_status,
                birthdate: props.patient?.birthdate.date_time,
                contact_number: props.patient?.contact_number,
                address: props.patient?.address,
            });

            selectedUser.value = props.patient?.user ?? null;
        }
    },
);

watch(selectedUser, (newSelectedUser) => {
    if (!newSelectedUser) return;

    setFieldValue('first_name', newSelectedUser.first_name);
    setFieldValue('last_name', newSelectedUser.last_name);
    setFieldValue('middle_name', newSelectedUser.middle_name);
});

const fetchUsers = useDebounceFn((query: string) => {
    axios
        .get(`/admin/users/search?q=${query}&role=patient&withoutRecord=true`)
        .then((response) => (users.value = response.data))
        .catch((error) => console.error(error));
}, 500);

const formatDate = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const birthdate = computed({
    get: () => (values.birthdate ? parseDate(values.birthdate) : undefined),
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
                <DialogTitle>Edit patient</DialogTitle>
                <DialogDescription>Update patient details and information.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updatePatient">
                <FormField name="user_id">
                    <FormItem
                        class="flex flex-col items-stretch justify-between gap-4 rounded-md border bg-background p-4 md:flex-row md:items-center"
                    >
                        <div>
                            <h4 class="mb-1 text-lg font-semibold">User <i>(optional)</i></h4>
                            <p class="text-xs text-zinc-600">Attach this record to a user</p>
                            <FormMessage />
                        </div>

                        <Combobox v-model="selectedUser">
                            <FormControl>
                                <ComboboxAnchor>
                                    <div class="relative w-full max-w-sm items-center">
                                        <ComboboxInput
                                            @update:modelValue="fetchUsers"
                                            :display-value="
                                                (val) =>
                                                    (selectedUser &&
                                                        getFullName(selectedUser?.last_name, selectedUser?.first_name, selectedUser?.middle_name)) ??
                                                    ''
                                            "
                                            placeholder="Select a user"
                                        />
                                        <ComboboxTrigger class="absolute inset-y-0 end-0 flex items-center justify-center px-3">
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </ComboboxTrigger>
                                    </div>
                                </ComboboxAnchor>
                            </FormControl>

                            <ComboboxList>
                                <ComboboxEmpty> No users found. </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="user"
                                        @select="
                                            () => {
                                                setFieldValue('user_id', user.id);
                                            }
                                        "
                                    >
                                        {{ user.last_name }}, {{ user.first_name }} {{ user.middle_name }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-1 gap-x-2 md:grid-cols-2">
                    <FormField
                        v-slot="{ componentField }"
                        name="first_name"
                    >
                        <FormItem>
                            <FormLabel required> First Name </FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    placeholder="e.g. Juan"
                                    :disabled="!!selectedUser"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="last_name"
                    >
                        <FormItem>
                            <FormLabel required> Last Name </FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    placeholder="e.g. Santos"
                                    :disabled="!!selectedUser"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="grid grid-cols-1 content-start gap-x-2 md:grid-cols-2">
                    <FormField
                        v-slot="{ componentField }"
                        name="middle_name"
                    >
                        <FormItem>
                            <FormLabel>Middle Name</FormLabel>

                            <FormControl>
                                <Input
                                    v-bind="componentField"
                                    placeholder="e.g. Dela Cruz"
                                    :disabled="!!selectedUser"
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField
                        v-slot="{ componentField }"
                        name="gender"
                    >
                        <FormItem>
                            <FormLabel required> Gender </FormLabel>

                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                </FormControl>

                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="male"> Male </SelectItem>
                                        <SelectItem value="female"> Female </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="grid grid-cols-1 content-start gap-x-2 md:grid-cols-2">
                    <FormField
                        v-slot="{ componentField }"
                        name="civil_status"
                    >
                        <FormItem>
                            <FormLabel required> Civil Status </FormLabel>

                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select civil status" />
                                    </SelectTrigger>
                                </FormControl>

                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="single"> Single </SelectItem>
                                        <SelectItem value="married"> Married </SelectItem>
                                        <SelectItem value="widowed"> Widowed </SelectItem>
                                        <SelectItem value="divorced"> Divorced </SelectItem>
                                        <SelectItem value="separated"> Separated </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField name="birthdate">
                        <FormItem>
                            <FormLabel required> Birthdate </FormLabel>

                            <Popover>
                                <PopoverAnchor>
                                    <PopoverTrigger as-child>
                                        <FormControl>
                                            <Button
                                                variant="outline"
                                                :class="cn('w-full text-start font-normal', !birthdate && 'text-muted-foreground')"
                                            >
                                                <span>{{ birthdate ? formatDate.format(toDate(birthdate)) : 'Select birthdate' }}</span>
                                                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
                                            </Button>
                                            <input hidden />
                                        </FormControl>
                                    </PopoverTrigger>
                                </PopoverAnchor>

                                <PopoverContent class="w-auto p-0">
                                    <Calendar
                                        :model-value="birthdate"
                                        calendar-label="Date of birth"
                                        initial-focus
                                        :min-value="new CalendarDate(1900, 1, 1)"
                                        :max-value="today(getLocalTimeZone())"
                                        @update:model-value="
                                            (v) => {
                                                if (v) {
                                                    setFieldValue('birthdate', v.toString());
                                                }
                                            }
                                        "
                                    />
                                </PopoverContent>
                            </Popover>

                            <FormDescription>Age is calculated from birthdate.</FormDescription>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <FormField
                    v-slot="{ componentField }"
                    name="contact_number"
                >
                    <FormItem>
                        <FormLabel required> Contact Number </FormLabel>

                        <FormControl>
                            <Input
                                v-bind="componentField"
                                placeholder="e.g. 09XXXXXXXXX"
                            />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="address"
                >
                    <FormItem>
                        <FormLabel required> Address </FormLabel>

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
                        Update patient record
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
