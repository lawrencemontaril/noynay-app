<script setup lang="ts">
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import { Invoice } from '@/types';
import { ALL_SERVICES } from '@/types/constants';
import { router } from '@inertiajs/vue3';
import { HandCoins, RefreshCcw } from 'lucide-vue-next';
import { ref } from 'vue';
import CreatePaymentDialog from './CreatePaymentDialog.vue';
import Button from './ui/button/Button.vue';

defineProps<{
    unpaidInvoices: Invoice[];
}>();

const { getFullName, formatCurrency } = useFormatters();
const { hasPermissionTo } = usePermissions();

const isRefreshing = ref(false);

const selectedInvoice = ref<Invoice | null>(null);
const isCreatePaymentDialogOpen = ref(false);

function openCreatePaymentDialog(invoice: Invoice) {
    if (!invoice) return;
    selectedInvoice.value = invoice;
    isCreatePaymentDialogOpen.value = true;
}

function refreshInvoices() {
    isRefreshing.value = true;
    router.reload({
        only: ['unpaidInvoices'],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}
</script>

<template>
    <div class="mb-4 rounded-lg border shadow-xs">
        <div class="flex items-center justify-between gap-4 border-b p-4">
            <h4 class="text-lg font-semibold">Unpaid invoices</h4>

            <Button
                variant="outline"
                @click="refreshInvoices"
                :disabled="isRefreshing"
            >
                <RefreshCcw :class="{ 'animate-spin': isRefreshing }" />
                <span>{{ isRefreshing ? 'Refreshing' : 'Refresh' }}</span>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>Patient Name</TableHead>
                    <TableHead>Service</TableHead>
                    <TableHead>Balance</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow
                    v-for="(unpaidInvoice, index) in unpaidInvoices"
                    :key="unpaidInvoice.id"
                >
                    <TableCell>{{ index + 1 }}</TableCell>

                    <TableCell>{{
                        getFullName(
                            unpaidInvoice?.appointment?.patient!.last_name!,
                            unpaidInvoice?.appointment?.patient!.first_name!,
                            unpaidInvoice?.appointment?.patient!.middle_name!,
                        )
                    }}</TableCell>

                    <TableCell class="max-w-48 truncate capitalize">
                        {{
                            ALL_SERVICES.find((type) => type.value === unpaidInvoice?.appointment?.type)?.label ||
                            unpaidInvoice?.appointment?.type ||
                            'N/A'
                        }}
                    </TableCell>

                    <TableCell>
                        {{ formatCurrency(unpaidInvoice.balance) }}
                    </TableCell>

                    <TableCell>
                        <div class="flex items-center gap-2">
                            <Button
                                v-if="hasPermissionTo('payments:create')"
                                @click="openCreatePaymentDialog(unpaidInvoice)"
                                size="icon"
                            >
                                <HandCoins />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>

                <TableEmpty
                    v-if="!unpaidInvoices.length"
                    :colspan="5"
                >
                    No invoices with unpaid invoices.
                </TableEmpty>
            </TableBody>
        </Table>

        <CreatePaymentDialog
            v-model:open="isCreatePaymentDialogOpen"
            :invoice="selectedInvoice"
            :patient="selectedInvoice?.appointment?.patient!"
        />
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% {
        transform: rotate(-360deg);
    }
}
</style>
