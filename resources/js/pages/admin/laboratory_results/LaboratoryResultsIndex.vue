<script setup lang="ts">
import Container from '@/components/Container.vue';
import DeleteLaboratoryResultDialog from '@/components/DeleteLaboratoryResultDialog.vue';
import EditLaboratoryResultDialog from '@/components/EditLaboratoryResultDialog.vue';
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
import { BreadcrumbItem, LaboratoryResult, PaginatedData } from '@/types';
import { LAB_TYPES } from '@/types/constants';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Eye, Pencil, Search, Trash, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    laboratory_results: PaginatedData<LaboratoryResult>;
    filters: { q?: string; type?: string; status?: string };
}>();

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laboratory Results',
        href: route('admin.laboratory_results.index'),
    },
];

const selectedLaboratoryResult = ref<LaboratoryResult | null>(null);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

function openEditDialog(laboratoryResult: LaboratoryResult) {
    selectedLaboratoryResult.value = laboratoryResult;
    isEditDialogOpen.value = true;
}

function openDeleteDialog(laboratoryResult: LaboratoryResult) {
    selectedLaboratoryResult.value = laboratoryResult;
    isDeleteDialogOpen.value = true;
}

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? 'all');
const type = ref(props.filters.type ?? 'all');

const filterLaboratoryResults = useDebounceFn(() => {
    router.get(
        route('admin.laboratory_results.index'),
        { q: q.value, status: status.value, type: type.value },
        { preserveState: true, replace: true },
    );
}, 400);

function clearSearch() {
    q.value = '';
    filterLaboratoryResults();
}

watch([q, status, type], () => filterLaboratoryResults());

const statuses = [
    { label: 'Pending', value: 'pending' },
    { label: 'Released', value: 'released' },
];

const laboratoryResultStatusBadgeMap: Record<LaboratoryResult['status'], BadgeVariants['variant']> = {
    pending: 'warning',
    released: 'default',
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <form
                @submit.prevent="filterLaboratoryResults"
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
                                <Select v-model="type">
                                    <SelectTrigger> Type: <SelectValue placeholder="Type" /> </SelectTrigger>
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
                                <Select v-model="status">
                                    <SelectTrigger> Status: <SelectValue placeholder="Status" /> </SelectTrigger>
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

                            <TableHead
                                v-if="
                                    hasAnyPermissionTo([
                                        'laboratory_results:view',
                                        'laboratory_results:update',
                                        'laboratory_results:delete',
                                    ])
                                "
                            >
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(laboratory_result, index) in laboratory_results.data"
                            :key="laboratory_result.id"
                        >
                            <TableCell>{{ (laboratory_results.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{
                                    getFullName(
                                        laboratory_result?.appointment?.patient?.last_name!,
                                        laboratory_result?.appointment?.patient?.first_name!,
                                        laboratory_result?.appointment?.patient?.middle_name!,
                                    )
                                }}
                            </TableCell>

                            <TableCell class="max-w-48 truncate capitalize">
                                {{
                                    LAB_TYPES.find((type) => type.value === laboratory_result.type)?.label ||
                                    laboratory_result.type ||
                                    'N/A'
                                }}
                            </TableCell>

                            <TableCell class="capitalize">
                                <Badge :variant="laboratoryResultStatusBadgeMap[laboratory_result.status]">
                                    {{ laboratory_result.status }}
                                </Badge>
                            </TableCell>

                            <TableCell
                                v-if="
                                    hasAnyPermissionTo([
                                        'laboratory_results:view',
                                        'laboratory_results:update',
                                        'laboratory_results:delete',
                                    ])
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('laboratory_results:view')"
                                        variant="info"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                route('admin.patients.appointments.laboratory_results.show', {
                                                    patient: laboratory_result.appointment?.patient?.id,
                                                    appointment: laboratory_result.appointment?.id,
                                                    laboratoryResult: laboratory_result.id,
                                                })
                                            "
                                            prefetch
                                        >
                                            <Eye />
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('laboratory_results:update')"
                                        @click="openEditDialog(laboratory_result)"
                                        variant="warning"
                                        size="icon"
                                    >
                                        <Pencil />
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('laboratory_results:delete')"
                                        @click="openDeleteDialog(laboratory_result)"
                                        variant="destructive"
                                        size="icon"
                                    >
                                        <Trash />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!laboratory_results.data.length"
                            :colspan="6"
                        >
                            No laboratory results.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <EditLaboratoryResultDialog
                    v-model:open="isEditDialogOpen"
                    :patient="selectedLaboratoryResult?.appointment?.patient"
                    :laboratory_result="selectedLaboratoryResult"
                />

                <DeleteLaboratoryResultDialog
                    v-model:open="isDeleteDialogOpen"
                    :laboratory_result="selectedLaboratoryResult"
                />

                <Pagination :meta="laboratory_results.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
