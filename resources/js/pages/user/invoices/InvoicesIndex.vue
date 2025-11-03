<script setup lang="ts">
import Container from '@/components/Container.vue';
import ShowInvoiceDialog from '@/components/ShowInvoiceDialog.vue';
import { BadgeVariants } from '@/components/ui/badge';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Invoice, PaginatedData, Patient } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    patient: Patient;
    invoices: PaginatedData<Invoice>;
    filters: { status: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoices',
        href: route('invoices.index'),
    },
];

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const { formatCurrency } = useFormatters();

const selectedInvoice = ref<Invoice | null>(null);
const isShowDialogOpen = ref(false);

const openShowDialog = (invoice: Invoice) => {
    if (!invoice) return;
    selectedInvoice.value = invoice;
    isShowDialogOpen.value = true;
};

const inertiaForm = useForm({
    status: props.filters.status ?? 'all',
});

function filterInvoices() {
    inertiaForm.get(route('invoices.index'));
}

watch(() => [inertiaForm.status], filterInvoices);

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
            <div class="rounded-lg border shadow-xs">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
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
                            <TableHead v-if="hasAnyPermissionTo(['invoices:view', 'invoices:delete'])">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(invoice, index) in invoices.data"
                            :key="invoice.id"
                        >
                            <TableCell>{{ index + 1 }}</TableCell>

                            <TableCell class="capitalize">
                                <Badge :variant="statuses.find((s) => s.value === invoice.status)?.badge">
                                    {{ statuses.find((s) => s.value === invoice.status)?.label }}
                                </Badge>
                            </TableCell>

                            <TableCell>{{ formatCurrency(invoice.total) }}</TableCell>

                            <TableCell v-if="hasAnyPermissionTo(['invoices:view', 'invoices:delete'])">
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('invoices:view')"
                                        @click="openShowDialog(invoice)"
                                        variant="info"
                                        size="icon"
                                    >
                                        <Eye />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!invoices.data.length"
                            :colspan="5"
                        >
                            No invoices.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <ShowInvoiceDialog
                :patient="patient"
                :invoice="selectedInvoice"
                v-model:open="isShowDialogOpen"
            />
        </Container>
    </AppLayout>
</template>
