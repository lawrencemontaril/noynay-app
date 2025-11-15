<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Invoice, Patient } from '@/types';
import { INVOICE_STATUSES } from '@/types/constants';
import { FileText, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import DataCard from './DataCard.vue';

const props = defineProps<{
    open: boolean;
    patient?: Patient | null;
    invoice: Invoice | null;
}>();
const emit = defineEmits(['update:open']);

const { formatCurrency, getFullName } = useFormatters();
const { hasAnyRole } = usePermissions();

function closeDialog() {
    emit('update:open', false);
}

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
    <Dialog
        :open="open"
        @update:open="closeDialog"
    >
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Invoice #{{ invoice?.id }}</DialogTitle>
            </DialogHeader>

            <!-- Patient Info -->
            <DataCard
                title="Patient Information"
                :columns="2"
            >
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Name</label>
                    <p class="text-sm font-semibold">
                        {{ getFullName(patient?.last_name!, patient?.first_name!, patient?.middle_name!) }}
                    </p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Payment Status</label>
                    <p
                        class="text-sm font-semibold capitalize"
                        :class="{
                            'text-primary': invoice?.status === 'paid',
                            'text-destructive': invoice?.status === 'cancelled',
                            'text-warning': invoice?.status === 'unpaid' || invoice?.status === 'partially_paid',
                        }"
                    >
                        {{ INVOICE_STATUSES.find((status) => status.value === invoice?.status)?.label }}
                    </p>
                </div>
            </DataCard>

            <DataCard title="Invoice Items">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Price</TableHead>
                            <TableHead>Qty.</TableHead>
                            <TableHead class="text-right">Sum</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(invoiceItem, index) in invoice?.invoice_items"
                            :key="invoiceItem.id"
                        >
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ invoiceItem.description }}</TableCell>
                            <TableCell>{{ formatCurrency(invoiceItem.unit_price) }}</TableCell>
                            <TableCell>{{ invoiceItem.quantity }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(invoiceItem.line_total) }}</TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!invoice?.invoice_items?.length"
                            :colspan="5"
                        >
                            <p>No invoice items yet.</p>
                        </TableEmpty>
                    </TableBody>

                    <TableFooter v-if="invoice?.invoice_items?.length">
                        <TableRow>
                            <TableCell
                                colspan="4"
                                class="text-right"
                                >Subtotal</TableCell
                            >
                            <TableCell class="text-right">{{ formatCurrency(invoice!.subtotal) }}</TableCell>
                        </TableRow>

                        <TableRow v-if="invoice?.with_discount">
                            <TableCell
                                colspan="4"
                                class="text-right"
                                >Discount</TableCell
                            >
                            <TableCell class="text-right"> -{{ formatCurrency(invoice!.discount_amount) }} </TableCell>
                        </TableRow>

                        <TableRow>
                            <TableCell
                                colspan="4"
                                class="text-right"
                                >Subtotal After Discount</TableCell
                            >
                            <TableCell class="text-right">
                                {{ formatCurrency(invoice!.subtotal_after_discount) }}
                            </TableCell>
                        </TableRow>

                        <TableRow>
                            <TableCell
                                colspan="4"
                                class="text-right"
                                >VAT</TableCell
                            >
                            <TableCell class="text-right">
                                {{ formatCurrency(invoice!.vat_amount) }}
                            </TableCell>
                        </TableRow>

                        <TableRow>
                            <TableCell
                                colspan="4"
                                class="text-right font-semibold"
                                >Total</TableCell
                            >
                            <TableCell class="text-right font-semibold">
                                {{ formatCurrency(invoice!.total) }}
                            </TableCell>
                        </TableRow>
                    </TableFooter>
                </Table>
            </DataCard>

            <DataCard title="Payments">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead class="text-right">Amount</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(payment, index) in invoice?.payments"
                            :key="payment.id"
                        >
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ payment.created_at.formatted_date }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(payment.amount) }}</TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!invoice?.payments?.length"
                            :colspan="3"
                        >
                            <p>No payments yet.</p>
                        </TableEmpty>
                    </TableBody>

                    <TableFooter v-if="invoice?.payments?.length">
                        <TableRow>
                            <TableCell
                                colspan="2"
                                class="text-right"
                            >
                                Total
                            </TableCell>
                            <TableCell class="text-right">{{ formatCurrency(invoice!.total_paid) }}</TableCell>
                        </TableRow>
                    </TableFooter>
                </Table>
            </DataCard>

            <DialogFooter>
                <Button
                    v-if="hasAnyRole(['patient', 'cashier'])"
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

                <Button
                    variant="outline"
                    @click="closeDialog"
                >
                    Close
                </Button>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>
