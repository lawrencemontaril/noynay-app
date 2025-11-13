<script setup lang="ts">
import Container from '@/components/Container.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Appointment, BreadcrumbItem, PaginatedData } from '@/types';
import { ALL_SERVICES, APPOINTMENT_STATUSES } from '@/types/constants';
import { Link, useForm } from '@inertiajs/vue3';
import { Calendar, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import CancelAppointmentDialog from './CancelAppointmentDialog.vue';
import RescheduleAppointmentDialog from './RescheduleAppointmentDialog.vue';

const props = defineProps<{
    appointments: PaginatedData<Appointment>;
    filters: { type: string; status: string };
}>();

const { hasPermissionTo } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointments',
        href: route('appointments.index'),
    },
];

const selectedAppointment = ref<Appointment | null>(null);
const isRescheduleAppointmentDialogOpen = ref(false);
const isCancelAppointmentDialogOpen = ref(false);

const openRescheduleAppointmentDialog = (appointment: Appointment) => {
    selectedAppointment.value = appointment;
    isRescheduleAppointmentDialogOpen.value = true;
};

const openCancelAppointmentDialog = (appointment: Appointment) => {
    selectedAppointment.value = appointment;
    isCancelAppointmentDialogOpen.value = true;
};

const inertiaForm = useForm({
    status: props.filters.status ?? 'all',
    type: props.filters.type ?? 'all',
});

function filterAppointments() {
    inertiaForm.get(route('appointments.index'));
}

watch(() => [inertiaForm.status, inertiaForm.type], filterAppointments);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Teleport
            v-if="hasPermissionTo('appointments:create')"
            defer
            to="#sidebar-header"
        >
            <Button as-child>
                <Link :href="route('appointments.create')">Create an appointment</Link>
            </Button>
        </Teleport>

        <Container>
            <div class="rounded-lg border shadow-xs">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>

                            <TableHead>
                                <Select v-model="inertiaForm.type">
                                    <SelectTrigger>
                                        Type:
                                        <SelectValue
                                            placeholder="Type"
                                            class="max-w-48 truncate"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="service in ALL_SERVICES"
                                                :key="service.value"
                                                :value="service.value"
                                            >
                                                {{ service.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>

                            <TableHead>Schedule</TableHead>

                            <TableHead>
                                <Select v-model="inertiaForm.status">
                                    <SelectTrigger>
                                        Status:
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="status in APPOINTMENT_STATUSES"
                                                :key="status.value"
                                                :value="status.value"
                                            >
                                                {{ status.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>

                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(appointment, index) in appointments.data"
                            :key="appointment.id"
                        >
                            <TableCell>{{ (appointments.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell class="max-w-48 truncate capitalize">
                                {{
                                    ALL_SERVICES.find((type) => type.value === appointment.type)?.label ||
                                    appointment.type ||
                                    'N/A'
                                }}
                            </TableCell>

                            <TableCell>{{ appointment.scheduled_at.formatted_date }}</TableCell>

                            <TableCell class="capitalize">
                                <Badge
                                    :variant="
                                        APPOINTMENT_STATUSES.find((status) => status.value === appointment.status)
                                            ?.badge
                                    "
                                >
                                    {{
                                        APPOINTMENT_STATUSES.find((status) => status.value === appointment.status)
                                            ?.label
                                    }}
                                </Badge>
                            </TableCell>

                            <TableCell class="flex items-center gap-2">
                                <Button
                                    v-if="hasPermissionTo('appointments:reschedule') && appointment.is_reschedulable"
                                    @click="openRescheduleAppointmentDialog(appointment)"
                                    variant="warning"
                                    size="icon"
                                >
                                    <Calendar />
                                </Button>

                                <Button
                                    v-else
                                    variant="warning"
                                    size="icon"
                                    disabled
                                >
                                    <Calendar />
                                </Button>

                                <Button
                                    v-if="hasPermissionTo('appointments:cancel') && appointment.is_cancellable"
                                    @click="openCancelAppointmentDialog(appointment)"
                                    variant="destructive"
                                    size="icon"
                                >
                                    <X />
                                </Button>
                                <Button
                                    v-else
                                    variant="destructive"
                                    size="icon"
                                    disabled
                                >
                                    <X />
                                </Button>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!appointments.data.length"
                            :colspan="5"
                        >
                            No appointments.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <Pagination :meta="appointments.meta" />

                <RescheduleAppointmentDialog
                    v-model:open="isRescheduleAppointmentDialogOpen"
                    :appointment="selectedAppointment!"
                />

                <CancelAppointmentDialog
                    v-model:open="isCancelAppointmentDialogOpen"
                    :appointment="selectedAppointment!"
                />
            </div>
        </Container>
    </AppLayout>
</template>
