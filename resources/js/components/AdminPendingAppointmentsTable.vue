<script setup lang="ts">
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Appointment } from '@/types';
import { ALL_SERVICES, LAB_TYPES } from '@/types/constants';
import { router, usePage } from '@inertiajs/vue3';
import { Eye, RefreshCcw } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ShowAppointmentDialog from './ShowAppointmentDialog.vue';
import Button from './ui/button/Button.vue';

defineProps<{
    pendingAppointments: Appointment[];
}>();

const { getFullName } = useFormatters();
const { hasPermissionTo } = usePermissions();

const page = usePage();
const user = computed(() => page.props.auth.user);
const isRefreshing = ref(false);

const serviceOptions = computed(() => {
    if (user.value?.role === 'laboratory_staff') {
        return LAB_TYPES;
    }
    if (user.value?.role === 'doctor') {
        // Exclude lab types
        return ALL_SERVICES.filter((opt) => !LAB_TYPES.some((lab) => lab.value === opt.value));
    }
    return ALL_SERVICES;
});

const selectedAppointment = ref<Appointment | null>(null);
const isShowDialogOpen = ref(false);

function openShowDialog(appointment: Appointment) {
    if (!appointment) return;
    selectedAppointment.value = appointment;
    isShowDialogOpen.value = true;
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
            <h4 class="text-lg font-semibold">Pending appointments</h4>

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
                    v-for="(pendingAppointment, index) in pendingAppointments"
                    :key="pendingAppointment.id"
                >
                    <TableCell>{{ index + 1 }}</TableCell>

                    <TableCell>{{
                        getFullName(
                            pendingAppointment.patient!.last_name,
                            pendingAppointment.patient!.first_name,
                            pendingAppointment.patient!.middle_name,
                        )
                    }}</TableCell>

                    <TableCell class="max-w-48 truncate capitalize">
                        {{ serviceOptions.find((opt) => opt.value === pendingAppointment.type)?.label || pendingAppointment.type || 'N/A' }}
                    </TableCell>

                    <TableCell>{{ pendingAppointment.scheduled_at.formatted_date }}</TableCell>

                    <TableCell>
                        <div class="flex items-center gap-2">
                            <Button
                                v-if="hasPermissionTo('appointments:view')"
                                @click="openShowDialog(pendingAppointment)"
                                variant="info"
                                size="icon"
                            >
                                <Eye />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>

                <TableEmpty
                    v-if="!pendingAppointments.length"
                    :colspan="5"
                >
                    No pending appointments.
                </TableEmpty>
            </TableBody>
        </Table>

        <ShowAppointmentDialog
            v-if="selectedAppointment && selectedAppointment.patient"
            v-model:open="isShowDialogOpen"
            :appointment="selectedAppointment"
            :patient="selectedAppointment?.patient"
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
