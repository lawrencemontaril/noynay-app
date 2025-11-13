<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { cn } from '@/lib/utils';
import { Head, useForm as useInertiaForm } from '@inertiajs/vue3';
import { CalendarDate, DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { CalendarIcon, Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { useForm as useVeeForm } from 'vee-validate';
import { computed, ref } from 'vue';
import * as z from 'zod';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const inertiaForm = useInertiaForm({
    first_name: '',
    last_name: '',
    middle_name: null as string | null | undefined,
    email: '',
    password: '',
    password_confirmation: '',
    gender: '',
    civil_status: '',
    birthdate: '',
    contact_number: '',
    address: '',
});

const formSchema = toTypedSchema(
    z.object({
        first_name: z
            .string({ required_error: 'First Name is required.' })
            .max(80, 'Exceeded maximum character of 80.'),
        last_name: z.string({ required_error: 'Last Name is required.' }).max(80, 'Exceeded maximum character of 80.'),
        middle_name: z.string().max(80, 'Exceeded maximum character of 80.').or(z.null()).optional(),
        email: z.string({ required_error: 'Email address is required.' }).email(),
        password: z.string({ required_error: 'Password field is required.' }),
        password_confirmation: z.string({ required_error: 'Password confirmation field is required.' }),
        gender: z.enum(['male', 'female']),
        civil_status: z.enum(['single', 'married', 'widowed', 'divorced', 'separated']),
        birthdate: z.string({ required_error: 'Birthdate is required.' }),
        contact_number: z
            .string({ required_error: 'Contact Number is required.' })
            .max(24, 'Exceeded maximum character of 24.'),
        address: z.string({ required_error: 'Address is required.' }),
    }),
);

const { handleSubmit, values, setErrors, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
});

const registerUser = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.post(route('register'), {
        onError: (serverErrors) => setErrors(serverErrors),
        onFinish: () => inertiaForm.reset('password', 'password_confirmation'),
    });
});

const formatDate = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const birthdate = computed({
    get: () => (values.birthdate ? parseDate(values.birthdate) : undefined),
    set: (val) => val,
});
</script>

<template>
    <LandingLayout>
        <AuthBase
            title="Create an account"
            description="Enter your details below to create your account"
        >
            <Head title="Register" />

            <form
                @submit.prevent="registerUser"
                class="p-4 md:p-0"
            >
                <div class="grid grid-cols-1 gap-x-2 md:grid-cols-3">
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
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>

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
                                />
                            </FormControl>

                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <FormField
                    v-slot="{ componentField }"
                    name="email"
                >
                    <FormItem>
                        <FormLabel>Email Address</FormLabel>

                        <FormControl>
                            <Input
                                v-bind="componentField"
                                placeholder="e.g. juansantos@gmail.com"
                            />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="password"
                >
                    <FormItem>
                        <FormLabel required>Password</FormLabel>

                        <FormControl>
                            <div class="relative w-full">
                                <Input
                                    v-bind="componentField"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    :tabindex="2"
                                    placeholder="Password"
                                />

                                <Button
                                    @click="showPassword = !showPassword"
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="absolute top-1/2 right-1 -translate-y-1/2"
                                >
                                    <EyeOff v-if="showPassword" />
                                    <Eye v-else />
                                </Button>
                            </div>
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="password_confirmation"
                >
                    <FormItem>
                        <FormLabel required>Confirm Password</FormLabel>

                        <FormControl>
                            <div class="relative w-full">
                                <Input
                                    v-bind="componentField"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    :tabindex="2"
                                    placeholder="Password"
                                />

                                <Button
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="absolute top-1/2 right-1 -translate-y-1/2"
                                >
                                    <EyeOff v-if="showPasswordConfirmation" />
                                    <Eye v-else />
                                </Button>
                            </div>
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-1 content-start gap-x-2 md:grid-cols-3">
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
                                                :class="
                                                    cn(
                                                        'w-full border-input text-start font-normal',
                                                        !birthdate && 'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <span>{{
                                                    birthdate
                                                        ? formatDate.format(toDate(birthdate))
                                                        : 'Select birthdate'
                                                }}</span>
                                                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
                                            </Button>
                                            <input hidden />
                                        </FormControl>
                                    </PopoverTrigger>
                                </PopoverAnchor>

                                <PopoverContent class="w-auto p-0">
                                    <Calendar
                                        button-size="icon"
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

                <Button
                    type="submit"
                    size="lg"
                    class="w-full"
                    :disabled="inertiaForm.processing"
                >
                    <LoaderCircle
                        v-if="inertiaForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Create account
                </Button>
            </form>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="route('login')"
                    class="underline underline-offset-4"
                    >Log in</TextLink
                >
            </div>
        </AuthBase>
    </LandingLayout>
</template>
