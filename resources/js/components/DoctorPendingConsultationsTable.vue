<script setup lang="ts">
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Appointment } from '@/types';
import { ALL_SERVICES } from '@/types/constants';
import { Link, router } from '@inertiajs/vue3';
import { Ellipsis, Eye, Plus, RefreshCcw } from 'lucide-vue-next';
import { ref } from 'vue';
import CreateConsultationDialog from './CreateConsultationDialog.vue';
import ShowAppointmentDialog from './ShowAppointmentDialog.vue';
import Button from './ui/button/Button.vue';

defineProps<{
    approvedAppointments: Appointment[];
}>();

const { getFullName } = useFormatters();
const { hasPermissionTo } = usePermissions();

const isRefreshing = ref(false);

const selectedAppointment = ref<Appointment | null>(null);
const isShowAppointmentDialogOpen = ref(false);
const isCreateConsultationDialogOpen = ref(false);

function openShowAppointmentDialog(appointment: Appointment) {
    selectedAppointment.value = appointment;
    isShowAppointmentDialogOpen.value = true;
}

function openCreateConsultationDialog(appointment: Appointment) {
    selectedAppointment.value = appointment;
    isCreateConsultationDialogOpen.value = true;
}

function refreshAppointments() {
    isRefreshing.value = true;
    router.reload({
        only: ['pendingAppointments'],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}
</script>

<template>
    <div class="mb-4 rounded-lg border shadow-xs">
        <div class="flex items-center justify-between border-b p-4">
            <h4 class="text-lg font-semibold">Pending consultations</h4>

            <Button
                variant="outline"
                @click="refreshAppointments"
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
                    <TableHead>Type</TableHead>
                    <TableHead>Schedule</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow
                    v-for="(approvedAppointment, index) in approvedAppointments"
                    :key="approvedAppointment.id"
                >
                    <TableCell>{{ index + 1 }}</TableCell>

                    <TableCell>{{
                        getFullName(
                            approvedAppointment.patient!.last_name,
                            approvedAppointment.patient!.first_name,
                            approvedAppointment.patient!.middle_name,
                        )
                    }}</TableCell>

                    <TableCell class="max-w-48 truncate capitalize">
                        {{
                            ALL_SERVICES.find((service) => service.value === approvedAppointment.type)?.label ||
                            approvedAppointment.type ||
                            'N/A'
                        }}
                    </TableCell>

                    <TableCell>{{ approvedAppointment.scheduled_at.formatted_date }}</TableCell>

                    <TableCell class="flex items-center gap-2">
                        <Button
                            v-if="hasPermissionTo('appointments:view')"
                            @click="openShowAppointmentDialog(approvedAppointment)"
                            variant="info"
                            size="icon"
                        >
                            <Eye />
                        </Button>

                        <Button
                            v-if="hasPermissionTo('consultations:create')"
                            @click="openCreateConsultationDialog(approvedAppointment)"
                            size="icon"
                        >
                            <Plus />
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
                                        patient: approvedAppointment?.patient?.id,
                                        appointment: approvedAppointment.id,
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
                    v-if="!approvedAppointments.length"
                    :colspan="5"
                >
                    No appointments with pending consultations.
                </TableEmpty>
            </TableBody>
        </Table>

        <ShowAppointmentDialog
            v-model:open="isShowAppointmentDialogOpen"
            :appointment="selectedAppointment"
            :patient="selectedAppointment?.patient!"
        />

        <CreateConsultationDialog
            v-model:open="isCreateConsultationDialogOpen"
            :appointment="selectedAppointment"
            :patient="selectedAppointment?.patient!"
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
