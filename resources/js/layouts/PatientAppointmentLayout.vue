<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { usePermissions } from '@/composables/usePermissions';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    patientId: number;
    appointmentId: number;
}>();

const { hasPermissionTo } = usePermissions();

const sidebarNavItems: NavItem[] = [
    {
        title: 'Details',
        href: route('admin.patients.appointments.show', { patient: props.patientId, appointment: props.appointmentId }),
        isActive: route().current('admin.patients.appointments.show'),
        access: hasPermissionTo('appointments:view'),
    },
    {
        title: 'Invoice',
        href: route('admin.patients.appointments.invoice', { patient: props.patientId, appointment: props.appointmentId }),
        isActive: route().current('admin.patients.appointments.invoice'),
        access: hasPermissionTo('invoices:view'),
    },
    {
        title: 'Consultations',
        href: route('admin.patients.appointments.consultations', { patient: props.patientId, appointment: props.appointmentId }),
        isActive: route().current('admin.patients.appointments.consultations'),
        access: hasPermissionTo('consultations:view'),
    },
    {
        title: 'Laboratory Results',
        href: route('admin.patients.appointments.laboratory_results', { patient: props.patientId, appointment: props.appointmentId }),
        isActive: route().current('admin.patients.appointments.laboratory_results'),
        access: hasPermissionTo('laboratory_results:view'),
    },
];
</script>

<template>
    <div class="px-4 py-6">
        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-y-0 lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <template
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                    >
                        <Button
                            v-if="item.access"
                            variant="ghost"
                            :class="['w-full justify-start', { 'bg-muted': item.isActive }]"
                            as-child
                        >
                            <Link
                                :href="item.href"
                                prefetch
                            >
                                {{ item.title }}
                            </Link>
                        </Button>
                    </template>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1">
                <section class="space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
