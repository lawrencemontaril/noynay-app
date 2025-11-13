<script setup lang="ts">
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Appointment } from '@/types';
import { ALL_SERVICES } from '@/types/constants';
import { router } from '@inertiajs/vue3';
import { Plus, RefreshCcw } from 'lucide-vue-next';
import { ref } from 'vue';
import CreateInvoiceDialog from './CreateInvoiceDialog.vue';
import Button from './ui/button/Button.vue';

defineProps<{
    approvedAppointments: Appointment[];
}>();

const { getFullName } = useFormatters();
const { hasPermissionTo } = usePermissions();

const isRefreshing = ref(false);

const selectedAppointment = ref<Appointment | null>(null);
const isCreateInvoiceDialogOpen = ref(false);

function openCreateInvoiceDialog(appointment: Appointment) {
    if (!appointment) return;
    selectedAppointment.value = appointment;
    isCreateInvoiceDialogOpen.value = true;
}

function refreshAppointments() {
    isRefreshing.value = true;
    router.reload({
        only: ['approvedAppointments'],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}
</script>

<template>
    <div class="mb-4 rounded-lg border shadow-xs">
        <div class="flex items-center justify-between gap-4 border-b p-4">
            <h4 class="text-lg font-semibold">Pending invoices</h4>

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
                    <TableHead>Service</TableHead>
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
                            ALL_SERVICES.find((type) => type.value === approvedAppointment.type)?.label ||
                            approvedAppointment.type ||
                            'N/A'
                        }}
                    </TableCell>

                    <TableCell>{{ approvedAppointment.scheduled_at.formatted_date }}</TableCell>

                    <TableCell>
                        <div class="flex items-center gap-2">
                            <Button
                                v-if="hasPermissionTo('invoices:create') && !approvedAppointment?.invoice"
                                @click="openCreateInvoiceDialog(approvedAppointment)"
                                size="icon"
                            >
                                <Plus />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>

                <TableEmpty
                    v-if="!approvedAppointments.length"
                    :colspan="5"
                >
                    No appointments with pending invoices.
                </TableEmpty>
            </TableBody>
        </Table>

        <CreateInvoiceDialog
            v-model:open="isCreateInvoiceDialogOpen"
            :appointment="selectedAppointment"
            :patient="selectedAppointment?.patient!"
            :procedures="selectedAppointment?.procedures!"
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
