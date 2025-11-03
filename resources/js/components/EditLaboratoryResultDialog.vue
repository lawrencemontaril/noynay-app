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
import { LaboratoryResult, Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { watch } from 'vue';
import * as z from 'zod';
import DataCard from './DataCard.vue';
import InputError from './InputError.vue';
import Input from './ui/input/Input.vue';
import Textarea from './ui/textarea/Textarea.vue';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    laboratory_result: LaboratoryResult | null;
}>();
const emit = defineEmits(['update:open']);

const { getFullName } = useFormatters();

const inertiaForm = useInertiaForm({
    appointment_id: 0,
    description: null as string | null,
    type: '',
    results_file: null as File | null,
});

const formSchema = toTypedSchema(
    z.object({
        appointment_id: z.number({ required_error: 'Appointment is required.' }),
        description: z.string().nullable(),
        type: z.string({ required_error: 'Test Type is required.' }),
        results_file: z
            .instanceof(File, { message: 'A valid file is required.' })
            .refine((file) => file.type === 'application/pdf', {
                message: 'Only PDF files are allowed.',
            })
            .refine((file) => file.size <= 12 * 1024 * 1024, {
                message: 'File size must be less than 12MB.',
            })
            .optional()
            .nullable(),
    }),
);

const { handleSubmit, setErrors, resetForm, setFieldValue, setValues } = useVeeForm({
    validationSchema: formSchema,
});

watch(
    () => props.open,
    () => {
        console.log(props.laboratory_result);
        setValues({
            appointment_id: props.laboratory_result?.appointment_id,
            description: props.laboratory_result?.description,
            type: props.laboratory_result?.type,
            results_file: null,
        });
    },
    { immediate: true },
);

const updateLaboratoryResult = handleSubmit((validatedValues) => {
    Object.assign(inertiaForm, validatedValues);

    inertiaForm.post(route('admin.laboratory_results.update', props.laboratory_result?.id), {
        onError: (serverErrors) => setErrors(serverErrors),
        onSuccess: () => {
            inertiaForm.reset();
            resetForm();
            closeDialog();
        },
    });
});

function closeDialog() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Upload Laboratory Result #{{ laboratory_result?.id }}</DialogTitle>
                <DialogDescription>Update patient laboratory result details.</DialogDescription>
            </DialogHeader>

            <form
                @submit.prevent="updateLaboratoryResult"
                enctype="multipart/form-data"
            >
                <InputError
                    class="mb-4"
                    :message="inertiaForm.errors.appointment_id"
                />

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
                        <FormLabel required> Test Type </FormLabel>

                        <Select
                            v-bind="componentField"
                            disabled
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

                        <template v-if="laboratory_result?.results_file_path">
                            <Button
                                as="a"
                                :href="laboratory_result?.results_file_url"
                                target="_blank"
                            >
                                View existing results file
                            </Button>
                        </template>

                        <template v-else>
                            <Button
                                variant="outline"
                                disabled
                                >No existing results file attached.</Button
                            >
                        </template>

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
                        Upload laboratory result
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
