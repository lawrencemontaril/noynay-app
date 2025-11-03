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
import { LaboratoryResult } from '@/types';
import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    open: boolean;
    laboratory_result: LaboratoryResult | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:open', 'deleted']);

const inertiaForm = useInertiaForm({});

const deleteLaboratoryResult = () => {
    inertiaForm.delete(route('admin.laboratory_results.destroy', props.laboratory_result?.id), {
        onSuccess: () => {
            closeDialog();
        },
    });
};

function closeDialog() {
    emit('update:open', false);
}
</script>

<template>
    <AlertDialog
        :open="open"
        @update:open="closeDialog"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you sure you want to delete this laboratory result?</AlertDialogTitle>
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
                    @click="deleteLaboratoryResult"
                >
                    <LoaderCircle
                        v-if="inertiaForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Delete laboratory result
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
