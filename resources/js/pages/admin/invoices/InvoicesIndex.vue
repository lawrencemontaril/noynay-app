<script setup lang="ts">
import Container from '@/components/Container.vue';
import DeleteInvoiceDialog from '@/components/DeleteInvoiceDialog.vue';
import EditInvoiceDialog from '@/components/EditInvoiceDialog.vue';
import Pagination from '@/components/Pagination.vue';
import { BadgeVariants } from '@/components/ui/badge';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Invoice, PaginatedData } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Eye, Pencil, Search, Trash, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    invoices: PaginatedData<Invoice>;
    filters: { q?: string; status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoices',
        href: route('admin.invoices.index'),
    },
];

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const { formatCurrency, getFullName } = useFormatters();

const selectedInvoice = ref<Invoice | null>(null);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

function openEditDialog(invoice: Invoice) {
    selectedInvoice.value = invoice;
    isEditDialogOpen.value = true;
}

function openDeleteDialog(invoice: Invoice) {
    selectedInvoice.value = invoice;
    isDeleteDialogOpen.value = true;
}

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? 'all');

const filterInvoices = useDebounceFn(() => {
    router.get(
        route('admin.invoices.index'),
        { q: q.value, status: status.value },
        { preserveState: true, replace: true },
    );
}, 400);

function clearSearch() {
    q.value = '';
    filterInvoices();
}

watch([q, status], () => filterInvoices());

const statuses: {
    label: string;
    value: Invoice['status'];
    badge: BadgeVariants['variant'];
}[] = [
    { label: 'Unpaid', value: 'unpaid', badge: 'warning' },
    { label: 'Partially Paid', value: 'partially_paid', badge: 'warning' },
    { label: 'Paid', value: 'paid', badge: 'default' },
    { label: 'Cancelled', value: 'cancelled', badge: 'destructive' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <form
                @submit.prevent="filterInvoices"
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

                            <TableHead>
                                <Select v-model="status">
                                    <SelectTrigger>
                                        Status:
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="status in statuses"
                                                :key="status.value"
                                                :value="status.value"
                                            >
                                                {{ status.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>

                            <TableHead>Total</TableHead>

                            <TableHead
                                v-if="hasAnyPermissionTo(['invoices:view', 'invoices:update', 'invoices:delete'])"
                            >
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(invoice, index) in invoices.data"
                            :key="invoice.id"
                        >
                            <TableCell>{{ (invoices.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{
                                    getFullName(
                                        invoice?.appointment?.patient?.last_name!,
                                        invoice?.appointment?.patient?.first_name!,
                                        invoice?.appointment?.patient?.middle_name!,
                                    )
                                }}
                            </TableCell>

                            <TableCell>
                                <Badge :variant="statuses.find((s) => s.value === invoice.status)?.badge">
                                    {{ statuses.find((s) => s.value === invoice.status)?.label }}
                                </Badge>
                            </TableCell>

                            <TableCell>
                                {{ formatCurrency(invoice.total) }}
                            </TableCell>

                            <TableCell
                                v-if="hasAnyPermissionTo(['invoices:view', 'invoices:update', 'invoices:delete'])"
                            >
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('invoices:view')"
                                        variant="info"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                route('admin.patients.appointments.invoice', {
                                                    patient: invoice?.appointment?.patient?.id,
                                                    appointment: invoice?.appointment?.id,
                                                })
                                            "
                                            prefetch
                                        >
                                            <Eye />
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('invoices:update')"
                                        @click="openEditDialog(invoice)"
                                        variant="warning"
                                        size="icon"
                                    >
                                        <Pencil />
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('invoices:delete')"
                                        @click="openDeleteDialog(invoice)"
                                        variant="destructive"
                                        size="icon"
                                    >
                                        <Trash />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!invoices.data.length"
                            :colspan="6"
                        >
                            No invoices.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <EditInvoiceDialog
                    v-model:open="isEditDialogOpen"
                    :patient="selectedInvoice?.appointment?.patient"
                    :appointment="selectedInvoice?.appointment"
                    :invoice="selectedInvoice"
                />

                <DeleteInvoiceDialog
                    v-model:open="isDeleteDialogOpen"
                    :invoice="selectedInvoice"
                />

                <Pagination :meta="invoices.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
