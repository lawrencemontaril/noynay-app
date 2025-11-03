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
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { User } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { watch } from 'vue';
import * as z from 'zod';
import Switch from './ui/switch/Switch.vue';

const props = defineProps<{
    open: boolean;
    user: User | null;
}>();
const emit = defineEmits(['update:open']);

function closeDialog() {
    emit('update:open', false);
}

const inertiaForm = useInertiaForm({
    first_name: '',
    last_name: '',
    middle_name: null as string | null,
    email: '',
    role: '',
    is_active: false,
});

const formSchema = toTypedSchema(
    z.object({
        first_name: z.string({ required_error: 'First name field is required.' }).max(80),
        last_name: z.string({ required_error: 'Last name field is required.' }).max(80),
        middle_name: z.string().max(80).optional().nullable(),
        email: z
            .string({ required_error: 'Email address field is required.' })
            .email({ message: 'Must be a valid email address.' })
            .max(255),
        role: z.string({ required_error: 'Role field is required. ' }),
        is_active: z.boolean({ required_error: 'Account status field is required.' }),
    }),
);

const { handleSubmit, setErrors, resetForm, setValues } = useVeeForm({
    validationSchema: formSchema,
    initialValues: inertiaForm,
});

const updateUser = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.patch(route('admin.users.update', props.user?.id), {
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

        if (props.user) {
            setValues({
                first_name: props.user?.first_name,
                last_name: props.user?.last_name,
                middle_name: props.user?.middle_name,
                email: props.user?.email,
                role: props.user?.role,
                is_active: props.user?.is_active,
            });
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
                <DialogTitle>Edit user</DialogTitle>
                <DialogDescription>Update user details and credentials.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updateUser">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
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
                            <FormLabel> Middle Name </FormLabel>

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
                        <FormLabel required> Email </FormLabel>

                        <FormControl>
                            <Input
                                v-bind="componentField"
                                type="email"
                                placeholder="e.g. juansantos@example.com"
                            />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ componentField }"
                    name="role"
                >
                    <FormItem
                        class="flex flex-col justify-between gap-4 rounded-lg border border-input bg-background p-4 shadow-xs md:flex-row md:items-center"
                    >
                        <div class="space-y-0.5">
                            <FormLabel
                                class="text-base capitalize"
                                required
                            >
                                Role
                            </FormLabel>
                            <FormDescription>What role should this user have?</FormDescription>
                        </div>

                        <Select v-bind="componentField">
                            <FormControl>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                            </FormControl>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="admin"> Administrator </SelectItem>
                                    <SelectItem value="system_admin"> System Administrator </SelectItem>
                                    <SelectItem value="doctor"> Doctor </SelectItem>
                                    <SelectItem value="laboratory_staff"> Laboratory Staff </SelectItem>
                                    <SelectItem value="cashier"> Cashier </SelectItem>
                                    <SelectItem value="patient"> Patient </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ value, handleChange }"
                    name="is_active"
                >
                    <FormItem
                        class="flex flex-row items-center justify-between gap-4 rounded-lg border border-input bg-background p-4 shadow-xs"
                    >
                        <div class="space-y-0.5">
                            <FormLabel
                                class="text-base capitalize"
                                required
                            >
                                Account Status
                            </FormLabel>
                            <FormDescription>Should this user be active?</FormDescription>
                        </div>

                        <FormControl>
                            <Switch
                                :model-value="value"
                                @update:model-value="handleChange"
                            />
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
                        Update user
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
