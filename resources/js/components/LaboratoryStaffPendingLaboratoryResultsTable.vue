<script setup lang="ts">
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Appointment, LaboratoryResult } from '@/types';
import { LAB_TYPES } from '@/types/constants';
import { Link, router } from '@inertiajs/vue3';
import { Ellipsis, Eye, RefreshCcw, Upload } from 'lucide-vue-next';
import { ref } from 'vue';
import EditLaboratoryResultDialog from './EditLaboratoryResultDialog.vue';
import ShowAppointmentDialog from './ShowAppointmentDialog.vue';
import Button from './ui/button/Button.vue';

defineProps<{
    pendingLaboratoryResults: LaboratoryResult[];
}>();

const { getFullName } = useFormatters();
const { hasPermissionTo } = usePermissions();

const isRefreshing = ref(false);

const selectedLaboratoryResult = ref<LaboratoryResult | null>(null);
const isEditLaboratoryResultDialogOpen = ref(false);

function openEditLaboratoryResultDialog(laboratoryResult: LaboratoryResult) {
    selectedLaboratoryResult.value = laboratoryResult;
    isEditLaboratoryResultDialogOpen.value = true;
}

const selectedAppointment = ref<Appointment | null>(null);
const isShowAppointmentDialogOpen = ref(false);

function openShowAppointmentDialog(appointment: Appointment) {
    selectedAppointment.value = appointment;
    isShowAppointmentDialogOpen.value = true;
}

function refreshLaboratoryResults() {
    isRefreshing.value = true;
    router.reload({
        only: ['pendingLaboratoryResults'],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}
</script>

<template>
    <div class="rounded-lg border shadow-xs">
        <div class="flex items-center justify-between border-b p-4">
            <h4 class="text-lg font-semibold">Pending laboratory results</h4>

            <Button
                variant="outline"
                @click="refreshLaboratoryResults"
                :disabled="isRefreshing"
            >
                <RefreshCcw :class="{ 'animate-spin': isRefreshing }" />
                <span>{{ isRefreshing ? 'Refreshing' : 'Refresh' }}</span>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>Patient Name</TableHead>
                    <TableHead>Service</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow
                    v-for="(pendingLaboratoryResult, index) in pendingLaboratoryResults"
                    :key="pendingLaboratoryResult.id"
                >
                    <TableCell>{{ index + 1 }}</TableCell>

                    <TableCell>{{
                        getFullName(
                            pendingLaboratoryResult.appointment!.patient!.last_name,
                            pendingLaboratoryResult.appointment!.patient!.first_name,
                            pendingLaboratoryResult.appointment!.patient!.middle_name,
                        )
                    }}</TableCell>

                    <TableCell class="max-w-48 truncate capitalize">
                        {{ LAB_TYPES.find((type) => type.value === pendingLaboratoryResult.type)?.label }}
                    </TableCell>

                    <TableCell class="flex items-center gap-2">
                        <Button
                            v-if="hasPermissionTo('appointments:view')"
                            @click="openShowAppointmentDialog(pendingLaboratoryResult.appointment!)"
                            variant="info"
                            size="icon"
                        >
                            <Eye />
                        </Button>

                        <Button
                            v-if="
                                hasPermissionTo('laboratory_results:update') &&
                                !pendingLaboratoryResult.results_file_path
                            "
                            @click="openEditLaboratoryResultDialog(pendingLaboratoryResult)"
                            size="icon"
                        >
                            <Upload />
                        </Button>

                        <Button
                            v-if="hasPermissionTo('appointments:view')"
                            variant="outline"
                            size="sm"
                            class="flex items-center gap-1 text-xs"
                            as-child
                        >
                            <Link
                                :href="
                                    route('admin.patients.appointments.show', {
                                        patient: pendingLaboratoryResult?.appointment?.patient?.id,
                                        appointment: pendingLaboratoryResult?.appointment?.id,
                                    })
                                "
                                prefetch
                            >
                                <Ellipsis class="h-4 w-4" /> More details
                            </Link>
                        </Button>
                    </TableCell>
                </TableRow>

                <TableEmpty
                    v-if="!pendingLaboratoryResults.length"
                    :colspan="5"
                >
                    No pending laboratory results.
                </TableEmpty>
            </TableBody>
        </Table>

        <ShowAppointmentDialog
            v-model:open="isShowAppointmentDialogOpen"
            :patient="selectedAppointment?.patient"
            :appointment="selectedAppointment"
        />

        <EditLaboratoryResultDialog
            v-if="selectedLaboratoryResult?.appointment?.patient && selectedLaboratoryResult"
            v-model:open="isEditLaboratoryResultDialogOpen"
            :patient="selectedLaboratoryResult?.appointment?.patient"
            :laboratory_result="selectedLaboratoryResult"
        />
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% {
        transform: rotate(-360deg);
    }
}
</style>
