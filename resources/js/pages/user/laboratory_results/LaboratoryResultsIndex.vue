<script setup lang="ts">
import Container from '@/components/Container.vue';
import Pagination from '@/components/Pagination.vue';
import ShowLaboratoryResultDialog from '@/components/ShowLaboratoryResultDialog.vue';
import { BadgeVariants } from '@/components/ui/badge';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, LaboratoryResult, PaginatedData, Patient } from '@/types';
import { LAB_TYPES } from '@/types/constants';
import { useForm } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    patient: Patient;
    laboratory_results: PaginatedData<LaboratoryResult>;
    filters: { type: string; status: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laboratory Results',
        href: route('laboratory_results.index'),
    },
];

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();

const selectedLaboratoryResult = ref<LaboratoryResult | null>(null);
const isShowDialogOpen = ref(false);

function openShowDialog(laboratoryResult: LaboratoryResult) {
    if (!laboratoryResult) return;
    selectedLaboratoryResult.value = laboratoryResult;
    isShowDialogOpen.value = true;
}

const inertiaForm = useForm({
    type: props.filters.type ?? 'all',
    status: props.filters.status ?? 'all',
});

function filterLaboratoryResults() {
    inertiaForm.get(route('laboratory_results.index'));
}

watch(() => [inertiaForm.type], filterLaboratoryResults);

const statuses: {
    label: string;
    value: LaboratoryResult['status'];
    badge: BadgeVariants['variant'];
}[] = [
    { label: 'Pending', value: 'pending', badge: 'warning' },
    { label: 'Released', value: 'released', badge: 'default' },
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
                                <Select v-model="inertiaForm.type">
                                    <SelectTrigger>
                                        Type:
                                        <SelectValue placeholder="Type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="type in LAB_TYPES"
                                                :key="type.value"
                                                :value="type.value"
                                            >
                                                {{ type.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>

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

                            <TableHead v-if="hasAnyPermissionTo(['laboratory_results:view'])"> Actions </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(laboratory_result, index) in laboratory_results.data"
                            :key="laboratory_result.id"
                        >
                            <TableCell>{{ (laboratory_results.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{ LAB_TYPES.find((type) => type.value === laboratory_result.type)?.label }}
                            </TableCell>

                            <TableCell class="capitalize">
                                <Badge :variant="statuses.find((s) => s.value === laboratory_result.status)?.badge">
                                    {{ statuses.find((s) => s.value === laboratory_result.status)?.label }}
                                </Badge>
                            </TableCell>

                            <TableCell v-if="hasAnyPermissionTo(['laboratory_results:view'])">
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('laboratory_results:view')"
                                        @click="openShowDialog(laboratory_result)"
                                        variant="info"
                                        size="icon"
                                    >
                                        <Eye />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!laboratory_results.data.length"
                            :colspan="5"
                        >
                            No laboratory results.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <Pagination :meta="laboratory_results.meta" />

                <ShowLaboratoryResultDialog
                    v-model:open="isShowDialogOpen"
                    :patient="patient"
                    :laboratory_result="selectedLaboratoryResult"
                />
            </div>
        </Container>
    </AppLayout>
</template>
