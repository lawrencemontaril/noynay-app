<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import AuthBase from '@/layouts/AuthLayout.vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Head, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import * as z from 'zod';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const inertiaForm = useInertiaForm({
    email: '',
    password: '',
    remember: false as boolean,
});

const formSchema = toTypedSchema(
    z.object({
        email: z.string({ required_error: 'Email address is required' }).email(),
        password: z.string({ required_error: 'Password address is required' }),
        remember: z.boolean().default(false),
    }),
);

const { handleSubmit, values, setErrors } = useVeeForm({
    validationSchema: formSchema,
    initialValues: inertiaForm,
});

const submit = handleSubmit((validatedValues) => {
    inertiaForm.email = validatedValues.email;
    inertiaForm.password = validatedValues.password;
    inertiaForm.remember = validatedValues.remember;

    inertiaForm.post(route('login'), {
        onFinish: () => {
            inertiaForm.reset('password');
            values.password = '';
        },
        onError: (serverErrors) => {
            setErrors(serverErrors);
        },
    });
});
</script>

<template>
    <LandingLayout>
        <AuthBase
            title="We serve to make you better"
            description="Enter your email and password below to log in"
        >
            <Head title="Log in" />

            <div
                v-if="status"
                class="mb-4 text-center text-sm font-medium text-green-600"
            >
                {{ status }}
            </div>

            <form
                @submit.prevent="submit"
                class="flex flex-col"
            >
                <FormField
                    v-slot="{ componentField }"
                    name="email"
                >
                    <FormItem>
                        <FormLabel>Email address</FormLabel>
                        <FormControl>
                            <Input
                                v-bind="componentField"
                                autofocus
                                autocomplete="email"
                                :tabindex="1"
                                placeholder="email@example.com"
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
                        <div class="flex items-center justify-between">
                            <FormLabel>Password</FormLabel>
                            <TextLink
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm"
                                :tabindex="5"
                            >
                                Forgot password?
                            </TextLink>
                        </div>

                        <FormControl>
                            <Input
                                v-bind="componentField"
                                type="password"
                                autocomplete="current-password"
                                :tabindex="2"
                                placeholder="Password"
                            />
                        </FormControl>

                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField
                    v-slot="{ value, handleChange }"
                    type="checkbox"
                    name="remember"
                >
                    <FormItem class="flex flex-row items-center space-y-2 gap-x-3">
                        <FormControl class="mb-0">
                            <Checkbox
                                :model-value="value"
                                @update:model-value="handleChange"
                                :tabindex="3"
                            />
                        </FormControl>
                        <div class="space-y-1 leading-none">
                            <FormLabel class="normal-case">Keep me signed in</FormLabel>
                            <FormMessage />
                        </div>
                    </FormItem>
                </FormField>

                <Button
                    size="lg"
                    type="submit"
                    class="mb-4 w-full"
                    :disabled="inertiaForm.processing"
                    :tabindex="4"
                >
                    <LoaderCircle
                        v-if="inertiaForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Log in
                </Button>

                <div class="text-center text-sm text-muted-foreground">
                    Don't have an account?
                    <TextLink
                        :href="route('register')"
                        :tabindex="6"
                    >
                        Sign up
                    </TextLink>
                </div>
            </form>
        </AuthBase>
    </LandingLayout>
</template>
