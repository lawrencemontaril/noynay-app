<script setup lang="ts">
import Container from '@/components/Container.vue';
import DeleteAppointmentDialog from '@/components/DeleteAppointmentDialog.vue';
import EditAppointmentDialog from '@/components/EditAppointmentDialog.vue';
import Pagination from '@/components/Pagination.vue';
import RestoreAppointmentDialog from '@/components/RestoreAppointmentDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Appointment, BreadcrumbItem, PaginatedData } from '@/types';
import { ALL_SERVICES, APPOINTMENT_STATUSES, LAB_TYPES } from '@/types/constants';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Archive, ArchiveRestore, Eye, Pencil, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    appointments: PaginatedData<Appointment>;
    filters: { q: string; status: string; type: string; archived: number };
}>();

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointments',
        href: route('admin.appointments.index'),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);
const selectedAppointment = ref<Appointment | null>(null);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isRestoreDialogOpen = ref(false);

function openEditDialog(appointment: Appointment) {
    selectedAppointment.value = appointment;
    isEditDialogOpen.value = true;
}

function openDeleteDialog(appointment: Appointment) {
    selectedAppointment.value = appointment;
    isDeleteDialogOpen.value = true;
}

function openRestoreDialog(appointment: Appointment) {
    selectedAppointment.value = appointment;
    isRestoreDialogOpen.value = true;
}

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

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? 'all');
const type = ref(props.filters.type ?? 'all');
const archived = ref(props.filters.archived ?? 0);

const filterAppointments = useDebounceFn(() => {
    router.get(
        route('admin.appointments.index'),
        { q: q.value, status: status.value, type: type.value, archived: archived.value },
        { preserveState: true, replace: true },
    );
}, 400);

function clearSearch() {
    q.value = '';
    filterAppointments();
}

watch([q, status, type, archived], () => filterAppointments());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <form
                @submit.prevent="filterAppointments"
                class="mb-4"
            >
                <div class="relative flex h-9 items-center">
                    <Search class="absolute left-2 size-4 shrink-0 stroke-secondary-foreground" />

                    <Input
                        @keydown.enter.prevent
                        v-model="q"
                        class="pl-8"
                        placeholder="Search for patients"
                    />

                    <Button
                        v-if="filters.q"
                        variant="destructive"
                        size="icon"
                        @click="clearSearch"
                        class="absolute right-1 size-7"
                    >
                        <X />
                    </Button>
                </div>
            </form>

            <div class="rounded-lg border shadow-xs">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Patient Name</TableHead>
                            <TableHead>Schedule</TableHead>

                            <TableHead>
                                <Select v-model="type">
                                    <SelectTrigger> Type: <SelectValue placeholder="Type" /> </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="service in serviceOptions"
                                                :key="service.value"
                                                :value="service.value"
                                            >
                                                {{ service.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>

                            <TableHead>
                                <Select v-model="status">
                                    <SelectTrigger> Status: <SelectValue placeholder="Status" /> </SelectTrigger>
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

                            <TableHead
                                v-if="
                                    hasAnyPermissionTo([
                                        'appointments:view',
                                        'appointments:update',
                                        'appointments:delete',
                                    ])
                                "
                            >
                                <Select
                                    v-model="archived"
                                    :disabled="!hasPermissionTo('appointments:delete')"
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Records" />
                                    </SelectTrigger>
                                    <SelectContent v-if="hasPermissionTo('appointments:delete')">
                                        <SelectGroup>
                                            <SelectItem :value="0">Active records</SelectItem>
                                            <SelectItem :value="1">Archived records</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(appointment, index) in appointments.data"
                            :key="appointment.id"
                        >
                            <TableCell>{{ (appointments.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{
                                    getFullName(
                                        appointment.patient!.last_name,
                                        appointment.patient!.first_name,
                                        appointment.patient!.middle_name,
                                    )
                                }}
                            </TableCell>

                            <TableCell>{{ appointment.scheduled_at.formatted_date }}</TableCell>

                            <TableCell class="max-w-48 truncate">
                                {{
                                    serviceOptions.find((service) => service.value === appointment.type)?.label ||
                                    appointment.type ||
                                    'N/A'
                                }}
                            </TableCell>

                            <TableCell>
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

                            <TableCell
                                v-if="
                                    hasAnyPermissionTo([
                                        'appointments:view',
                                        'appointments:update',
                                        'appointments:delete',
                                    ])
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('appointments:view') && !appointment.deleted_at"
                                        variant="info"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                route('admin.patients.appointments.show', {
                                                    patient: appointment?.patient?.id,
                                                    appointment: appointment.id,
                                                })
                                            "
                                            prefetch
                                        >
                                            <Eye />
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('appointments:update') && !appointment.deleted_at"
                                        @click="openEditDialog(appointment)"
                                        variant="warning"
                                        size="icon"
                                    >
                                        <Pencil />
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('appointments:delete') && !appointment.deleted_at"
                                        @click="openDeleteDialog(appointment)"
                                        variant="secondary"
                                        size="icon"
                                    >
                                        <Archive />
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('appointments:restore') && appointment.deleted_at"
                                        @click="openRestoreDialog(appointment)"
                                        size="icon"
                                    >
                                        <ArchiveRestore />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!appointments.data.length"
                            :colspan="6"
                        >
                            No appointments.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <EditAppointmentDialog
                    v-model:open="isEditDialogOpen"
                    :patient="selectedAppointment?.patient"
                    :appointment="selectedAppointment"
                />

                <DeleteAppointmentDialog
                    v-model:open="isDeleteDialogOpen"
                    :appointment="selectedAppointment"
                />

                <RestoreAppointmentDialog
                    v-model:open="isRestoreDialogOpen"
                    :appointment="selectedAppointment"
                />

                <Pagination :meta="appointments.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
