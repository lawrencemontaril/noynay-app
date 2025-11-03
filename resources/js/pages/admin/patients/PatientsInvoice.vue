<script setup lang="ts">
import CreateInvoiceItemDialog from '@/components/CreateInvoiceItemDialog.vue';
import CreatePaymentDialog from '@/components/CreatePaymentDialog.vue';
import EditInvoiceDialog from '@/components/EditInvoiceDialog.vue';
import PatientProfileTabs from '@/components/PatientProfileTabs.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import Separator from '@/components/ui/separator/Separator.vue';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientAppointmentLayout from '@/layouts/PatientAppointmentLayout.vue';
import { Appointment, BreadcrumbItem, Invoice, Patient } from '@/types';
import { INVOICE_STATUSES } from '@/types/constants';
import { FileText, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    patient: Patient;
    appointment: Appointment;
    invoice: Invoice | null;
}>();

const { hasRole, hasPermissionTo } = usePermissions();
const { formatCurrency } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: route('admin.patients.index'),
    },
    {
        title: `${props.patient.last_name}, ${props.patient.first_name} ${props.patient.middle_name ? `${props.patient.middle_name[0]}.` : ''}`,
        href: route('admin.patients.show', props.patient.id),
    },
    {
        title: 'Appointments',
        href: route('admin.patients.appointments', props.patient.id),
    },
    {
        title: 'Invoice',
        href: route('admin.patients.appointments.invoice', {
            patient: props.patient.id,
            appointment: props.appointment.id,
        }),
    },
];

const isEditInvoiceDialogOpen = ref(false);
const isCreateInvoiceItemDialogOpen = ref(false);
const isCreatePaymentDialogOpen = ref(false);

const isDownloading = ref(false);

const downloadInvoice = async () => {
    if (!props.invoice) return;

    try {
        isDownloading.value = true;
        const url = route('invoices.download', props.invoice.id);

        const response = await fetch(url, {
            headers: { Accept: 'application/pdf' },
            credentials: 'include',
        });

        if (!response.ok) throw new Error('Failed to download PDF');

        const blob = await response.blob();
        const fileUrl = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = `invoice-${props.invoice.id}.pdf`;
        document.body.appendChild(link);
        link.click();

        document.body.removeChild(link);
        URL.revokeObjectURL(fileUrl);
    } catch (error) {
        console.error(error);
    } finally {
        isDownloading.value = false;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <PatientProfileTabs :patient="patient" />

        <PatientAppointmentLayout
            :patient-id="patient.id"
            :appointment-id="appointment.id"
        >
            <Card
                v-if="invoice"
                class="mx-auto max-w-4xl shadow-xs"
            >
                <CardHeader class="flex items-center justify-between border-b">
                    <div>
                        <CardTitle class="inline-flex items-center gap-2 text-xl font-semibold">
                            Invoice #{{ invoice.id }}
                            <Badge
                                as="span"
                                :variant="INVOICE_STATUSES.find((status) => status.value === invoice?.status)?.badge"
                                class="capitalize"
                            >
                                {{ INVOICE_STATUSES.find((status) => status.value === invoice?.status)?.label }}
                            </Badge>
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">Issued on {{ invoice.created_at.formatted_date }}</p>
                    </div>

                    <div class="flex gap-2">
                        <Button
                            variant="destructive"
                            :disabled="isDownloading"
                            @click="downloadInvoice"
                        >
                            <LoaderCircle
                                v-if="isDownloading"
                                class="h-4 w-4 animate-spin"
                            />
                            <span
                                v-if="!isDownloading"
                                class="flex items-center gap-2"
                            >
                                <FileText />
                                PDF
                            </span>
                            <span v-else>Downloading...</span>
                        </Button>
                    </div>
                </CardHeader>

                <CardContent>
                    <!-- Invoice Items Table -->
                    <h2 class="mb-2 text-lg font-semibold">Invoice Items</h2>

                    <Table class="w-full border-collapse text-sm">
                        <TableHeader>
                            <TableRow class="border-b text-left">
                                <TableHead class="py-2 font-medium">Description</TableHead>
                                <TableHead class="py-2 text-center font-medium">Qty</TableHead>
                                <TableHead class="py-2 text-right font-medium">Unit Price</TableHead>
                                <TableHead class="py-2 text-right font-medium">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in invoice.invoice_items"
                                :key="item.id"
                                class="border-b last:border-0 hover:bg-muted/30"
                            >
                                <TableCell class="py-2">{{ item.description }}</TableCell>
                                <TableCell class="py-2 text-center">{{ item.quantity }}</TableCell>
                                <TableCell class="py-2 text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
                                <TableCell class="py-2 text-right font-medium">{{
                                    formatCurrency(item.line_total)
                                }}</TableCell>
                            </TableRow>
                            <TableEmpty
                                v-if="!invoice.invoice_items?.length"
                                :colspan="4"
                            >
                                No items in this invoice.
                            </TableEmpty>
                        </TableBody>
                    </Table>

                    <Button
                        v-if="hasRole('cashier') && appointment.is_operatable"
                        @click="isCreateInvoiceItemDialogOpen = true"
                        class="mt-4 w-full"
                    >
                        Add invoice items
                    </Button>

                    <Button
                        v-if="
                            invoice &&
                            hasRole('doctor') &&
                            hasPermissionTo('invoices:update') &&
                            ['approved', 'completed'].includes(appointment.status)
                        "
                        @click="isEditInvoiceDialogOpen = true"
                        class="mt-4 w-full"
                    >
                        Add/Edit/Remove items
                    </Button>

                    <Separator class="my-4" />

                    <!-- Payments Table -->
                    <h2 class="mb-2 text-lg font-semibold">Payments</h2>
                    <div class="overflow-x-auto">
                        <Table class="w-full border-collapse text-sm">
                            <TableHeader>
                                <TableRow class="border-b text-left">
                                    <TableHead class="py-2 font-medium">Date</TableHead>
                                    <TableHead class="py-2 text-right font-medium">Amount</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="payment in invoice.payments"
                                    :key="payment.id"
                                    class="border-b last:border-0 hover:bg-muted/30"
                                >
                                    <TableCell class="py-2">{{ payment.created_at.formatted_date }}</TableCell>
                                    <TableCell class="py-2 text-right">{{ formatCurrency(payment.amount) }}</TableCell>
                                </TableRow>
                                <TableEmpty
                                    v-if="!invoice.payments?.length"
                                    :colspan="2"
                                >
                                    No payments in this invoice.
                                </TableEmpty>
                            </TableBody>
                        </Table>
                    </div>

                    <Button
                        v-if="
                            invoice &&
                            invoice?.balance > 0 &&
                            hasRole('cashier') &&
                            hasPermissionTo('payments:create') &&
                            ['approved', 'completed'].includes(appointment.status)
                        "
                        @click="isCreatePaymentDialogOpen = true"
                        class="mt-4 w-full"
                    >
                        Add payment
                    </Button>
                </CardContent>

                <CardFooter class="flex flex-col items-end gap-2">
                    <div class="flex w-full justify-end border-t pt-4">
                        <div class="text-right">
                            <p class="text-sm text-muted-foreground">Total:</p>
                            <p class="text-xl font-semibold">{{ formatCurrency(invoice.total) }}</p>
                            <p class="text-sm text-muted-foreground">Total Paid:</p>
                            <p class="text-xl font-semibold text-primary">{{ formatCurrency(invoice.total_paid) }}</p>
                            <p
                                v-if="invoice.balance > 0"
                                class="text-sm font-medium text-destructive"
                            >
                                Outstanding balance: {{ formatCurrency(invoice.balance) }}
                            </p>
                        </div>
                    </div>
                </CardFooter>
            </Card>

            <!-- Empty state -->
            <div
                v-if="!invoice"
                class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/20 p-8 text-center"
            >
                <p class="text-sm text-muted-foreground">No invoice yet.</p>
            </div>

            <CreateInvoiceItemDialog
                v-if="hasRole('cashier')"
                v-model:open="isCreateInvoiceItemDialogOpen"
                :patient="patient"
                :appointment="appointment"
                :invoice="invoice"
            />

            <EditInvoiceDialog
                v-if="hasRole('doctor')"
                v-model:open="isEditInvoiceDialogOpen"
                :patient="patient"
                :appointment="appointment"
                :invoice="invoice"
            />

            <CreatePaymentDialog
                v-if="hasRole('cashier')"
                v-model:open="isCreatePaymentDialogOpen"
                :invoice="invoice"
                :patient="patient"
            />
        </PatientAppointmentLayout>
    </AppLayout>
</template>
