<script setup lang="ts">
import Container from '@/components/Container.vue';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Appointment, type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps<{
    last_appointment: Appointment | null;
    total_unpaid_amount: number;
    total_paid_amount: number;
}>();

const { formatCurrency } = useFormatters();
const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();

const page = usePage();
const user = page.props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: `Hello, ${user.first_name} ${user.last_name}`,
        href: route('dashboard'),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <template v-if="hasAnyPermissionTo(['appointments:create', 'users:create'])">
                <p class="mb-2 text-xs font-semibold text-muted-foreground uppercase">Quick Actions</p>
                <div class="mb-4 flex flex-wrap gap-2 rounded-lg border p-2 shadow-xs">
                    <Button
                        v-if="hasPermissionTo('appointments:create')"
                        as-child
                    >
                        <Link
                            :href="route('appointments.create')"
                            prefetch
                            >Create an appointment</Link
                        >
                    </Button>

                    <Button as-child>
                        <Link
                            :href="route('profile.edit')"
                            prefetch
                            >Edit profile</Link
                        >
                    </Button>
                </div>
            </template>

            <div class="my-4 grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-b-3 border-b-primary p-3 px-4 shadow-xs">
                    <span class="text-sm font-semibold text-muted-foreground">Unpaid balance</span>
                    <p class="text-2xl font-bold">{{ formatCurrency(total_unpaid_amount) }}</p>
                </div>

                <div class="rounded-lg border border-b-3 border-b-primary p-3 px-4 shadow-xs">
                    <span class="text-sm font-semibold text-muted-foreground">Paid balance</span>
                    <p class="text-2xl font-bold">{{ formatCurrency(total_paid_amount) }}</p>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
