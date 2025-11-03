<script setup lang="ts">
import AdminDashboard from '@/components/AdminDashboard.vue';
import CashierDashboard from '@/components/CashierDashboard.vue';
import Container from '@/components/Container.vue';
import CreatePatientDialog from '@/components/CreatePatientDialog.vue';
import CreateUserDialog from '@/components/CreateUserDialog.vue';
import DoctorDashboard from '@/components/DoctorDashboard.vue';
import LaboratoryStaffDashboard from '@/components/LaboratoryStaffDashboard.vue';
import SystemAdminDashboard from '@/components/SystemAdminDashboard.vue';
import Button from '@/components/ui/button/Button.vue';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Appointment, Invoice, LaboratoryResult, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    role: string;
    appointmentStatusChart: any;
    appointmentServiceChart: any;
    patientsByGenderChart: any;
    patientsByAgeGroupChart: any;
    patientsByCivilStatusChart: any;
    consultationsOverTimeChart: any;
    vitalSignsByAgeGroupChart: any;
    patientBmiChart: any;
    patientTemperatureChart: any;
    bloodPressureChart: any;
    oxygenSaturationChart: any;
    invoiceStatusChart: any;
    invoiceRevenuePerMonthChart: any;
    laboratoryResultsByTypeChart: any;
    userActiveStatusChart: any;
    userRegistrationsPerMonthChart: any;
    usersByRoleChart: any;

    // Admin-specific
    pendingAppointments: Appointment[];

    // Cashier, doctor-specific
    approvedAppointments: Appointment[];
    unpaidInvoices: Invoice[];

    // Laboratory staff-specific
    pendingLaboratoryResults: LaboratoryResult[];
}

defineProps<Props>();

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const page = usePage();
const user = page.props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: `Hello, ${user.first_name} ${user.last_name}`,
        href: route('admin.dashboard'),
    },
];

const dashboards: any = {
    admin: AdminDashboard,
    system_admin: SystemAdminDashboard,
    doctor: DoctorDashboard,
    laboratory_staff: LaboratoryStaffDashboard,
    cashier: CashierDashboard,
};

const isCreateUserDialogOpen = ref(false);
const isCreatePatientDialogOpen = ref(false);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <template v-if="hasAnyPermissionTo(['users:create', 'patients:create'])">
                <p class="mb-2 text-xs font-semibold text-muted-foreground uppercase">Quick Actions</p>
                <div class="mb-4 flex flex-wrap gap-2 rounded-lg border p-2 shadow-xs">
                    <template v-if="hasPermissionTo('users:create')">
                        <Button @click="isCreateUserDialogOpen = true"> Create a user </Button>

                        <CreateUserDialog v-model:open="isCreateUserDialogOpen" />
                    </template>

                    <template v-if="hasPermissionTo('patients:create')">
                        <Button @click="isCreatePatientDialogOpen = true"> Create a patient </Button>

                        <CreatePatientDialog v-model:open="isCreatePatientDialogOpen" />
                    </template>
                </div>
            </template>

            <component
                :is="dashboards[user.role]"
                :appointmentStatusChart="appointmentStatusChart"
                :appointmentServiceChart="appointmentServiceChart"
                :patientsByGenderChart="patientsByGenderChart"
                :patientsByAgeGroupChart="patientsByAgeGroupChart"
                :patientsByCivilStatusChart="patientsByCivilStatusChart"
                :consultationsOverTimeChart="consultationsOverTimeChart"
                :vitalSignsByAgeGroupChart="vitalSignsByAgeGroupChart"
                :patientBmiChart="patientBmiChart"
                :patientTemperatureChart="patientTemperatureChart"
                :bloodPressureChart="bloodPressureChart"
                :oxygenSaturationChart="oxygenSaturationChart"
                :invoiceStatusChart="invoiceStatusChart"
                :invoiceRevenuePerMonthChart="invoiceRevenuePerMonthChart"
                :laboratoryResultsByTypeChart="laboratoryResultsByTypeChart"
                :userActiveStatusChart="userActiveStatusChart"
                :userRegistrationsPerMonthChart="userRegistrationsPerMonthChart"
                :usersByRoleChart="usersByRoleChart"
                :pendingAppointments="pendingAppointments"
                :approvedAppointments="approvedAppointments"
                :pendingLaboratoryResults="pendingLaboratoryResults"
                :unpaidInvoices="unpaidInvoices"
            />
        </Container>
    </AppLayout>
</template>
