<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ClipboardList,
    FlaskConical,
    FolderHeart,
    LayoutGrid,
    MessagesSquare,
    ReceiptText,
    ScrollText,
    Table2,
    User,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from './ui/collapsible';

const { hasPermissionTo, hasRole, hasAnyRole } = usePermissions();

const adminAnalyticsNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('admin.dashboard'),
        icon: LayoutGrid,
        isActive: route().current('admin.dashboard'),
        access: true,
    },
];

const adminReportsNavItems: NavItem[] = [
    {
        title: 'Patient',
        href: route('admin.reports.patient'),
        icon: FolderHeart,
        isActive: route().current('admin.reports.patient'),
        access: hasPermissionTo('patients:view_any'),
    },
    {
        title: 'Appointment',
        href: route('admin.reports.appointment'),
        icon: ClipboardList,
        isActive: route().current('admin.reports.appointment'),
        access: hasPermissionTo('appointments:view_any'),
    },
    {
        title: 'Invoice',
        href: route('admin.reports.invoice'),
        icon: ReceiptText,
        isActive: route().current('admin.reports.invoice'),
        access: hasPermissionTo('invoices:view_any'),
    },
    {
        title: 'Consultation',
        href: route('admin.reports.consultation'),
        icon: MessagesSquare,
        isActive: route().current('admin.reports.consultation'),
        access: hasPermissionTo('consultations:view_any'),
    },
    {
        title: 'Laboratory',
        href: route('admin.reports.laboratory-result'),
        icon: FlaskConical,
        isActive: route().current('admin.reports.laboratory-result'),
        access: hasPermissionTo('laboratory_results:view_any'),
    },
];

const filteredAdminReportsNavItems = computed(() => {
    return adminReportsNavItems.filter((item) => item.access);
});

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

const page = usePage();
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
                            :href="route('home')"
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

            <SidebarMenu class="px-2 py-0">
                <Collapsible
                    :defaultOpen="route().current('admin.reports*')"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :is-active="route().current('admin.reports*')">
                                <Table2 />
                                <span>Reports</span>
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="reportsNavItem in filteredAdminReportsNavItems"
                                    :key="reportsNavItem.href"
                                >
                                    <SidebarMenuSubButton
                                        :is-active="reportsNavItem.href === page.url || reportsNavItem?.isActive"
                                        :tooltip="reportsNavItem.title"
                                        as-child
                                    >
                                        <Link
                                            :href="reportsNavItem.href"
                                            prefetch
                                        >
                                            <component :is="reportsNavItem.icon" />
                                            <span>{{ reportsNavItem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
            </SidebarMenu>

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
