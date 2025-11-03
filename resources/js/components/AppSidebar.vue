<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ClipboardList, FlaskConical, FolderHeart, LayoutGrid, MessagesSquare, ReceiptText, ScrollText, Table2, User } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const { hasPermissionTo, hasRole, hasAnyRole } = usePermissions();

const adminAnalyticsNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('admin.dashboard'),
        icon: LayoutGrid,
        isActive: route().current('admin.dashboard'),
        access: true,
    },
    {
        title: 'Reports',
        href: route('admin.reports'),
        icon: Table2,
        isActive: route().current('admin.reports'),
        access: hasAnyRole(['cashier', 'admin']),
    },
];

const adminMainNavItems: NavItem[] = [
    {
        title: 'Users',
        href: route('admin.users.index'),
        icon: User,
        isActive: route().current('admin.users.*'),
        access: hasAnyRole(['admin', 'system_admin']) && hasPermissionTo('users:view_any'),
    },
    {
        title: 'Patients',
        href: route('admin.patients.index'),
        icon: FolderHeart,
        isActive: route().current('admin.patients.*'),
        access: hasPermissionTo('patients:view_any'),
    },
    {
        title: 'Appointments',
        href: route('admin.appointments.index'),
        icon: ClipboardList,
        isActive: route().current('admin.appointments.*'),
        access: hasPermissionTo('appointments:view_any'),
    },
    {
        title: 'Consultations',
        href: route('admin.consultations.index'),
        icon: MessagesSquare,
        isActive: route().current('admin.consultations.*'),
        access: hasPermissionTo('consultations:view_any'),
    },
    {
        title: 'Invoices',
        href: route('admin.invoices.index'),
        icon: ReceiptText,
        isActive: route().current('admin.invoices.*'),
        access: hasPermissionTo('invoices:view_any'),
    },
    {
        title: 'Laboratory Results',
        href: route('admin.laboratory_results.index'),
        icon: FlaskConical,
        isActive: route().current('admin.laboratory_results.*'),
        access: hasPermissionTo('laboratory_results:view_any'),
    },
];

const userAnalyticsNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
        isActive: route().current('dashboard'),
        access: hasRole('patient'),
    },
];

const userMainNavItems: NavItem[] = [
    {
        title: 'Appointments',
        href: route('appointments.index'),
        icon: ClipboardList,
        isActive: route().current('appointments.*'),
        access: hasRole('patient'),
    },
    {
        title: 'Invoices',
        href: route('invoices.index'),
        icon: ScrollText,
        isActive: route().current('invoices.*'),
        access: hasRole('patient'),
    },
    {
        title: 'Consultations',
        href: route('consultations.index'),
        icon: MessagesSquare,
        isActive: route().current('consultations.*'),
        access: hasRole('patient'),
    },
    {
        title: 'Laboratory Results',
        href: route('laboratory_results.index'),
        icon: FlaskConical,
        isActive: route().current('laboratory_results.*'),
        access: hasRole('patient'),
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="sidebar"
    >
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                    >
                        <Link
                            :href="route('dashboard')"
                            prefetch
                        >
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent v-if="hasAnyRole(['admin', 'system_admin', 'doctor', 'laboratory_staff', 'cashier'])">
            <NavMain
                group-label="Analytics"
                :items="adminAnalyticsNavItems"
            />
            <NavMain
                group-label="Data"
                :items="adminMainNavItems"
            />
        </SidebarContent>

        <SidebarContent v-if="hasRole('patient')">
            <NavMain
                group-label="Analytics"
                :items="userAnalyticsNavItems"
            />
            <NavMain
                group-label="Data"
                :items="userMainNavItems"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
