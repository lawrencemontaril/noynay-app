<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import Button from '@/components/ui/button/Button.vue';
import { Patient } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    open: boolean;
    patient: Patient | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:open']);

const closeDialog = () => {
    emit('update:open', false);
};

const inertiaForm = useInertiaForm({});

const deleteConsultation = () => {
    inertiaForm.delete(route('admin.patients.destroy', props.patient?.id), {
        onSuccess: () => {
            closeDialog();
        },
    });
};
</script>

<template>
    <AlertDialog
        :open="open"
        @update:open="closeDialog"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you sure you want to delete this patient?</AlertDialogTitle>
                <AlertDialogDescription>This action cannot be undone.</AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <Button
                    variant="outline"
                    :disabled="inertiaForm.processing"
                    @click="closeDialog"
                >
                    Cancel
                </Button>

                <Button
                    type="submit"
                    variant="destructive"
                    :disabled="inertiaForm.processing"
                    @click="deleteConsultation"
                >
                    <LoaderCircle
                        v-if="inertiaForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Delete patient
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
