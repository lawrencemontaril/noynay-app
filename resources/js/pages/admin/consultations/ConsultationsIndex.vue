<script setup lang="ts">
import Container from '@/components/Container.vue';
import DeleteConsultationDialog from '@/components/DeleteConsultationDialog.vue';
import EditConsultationDialog from '@/components/EditConsultationDialog.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Consultation, PaginatedData } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Eye, Pencil, Search, Trash, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    consultations: PaginatedData<Consultation>;
    filters: { q: string };
}>();

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consultations',
        href: route('admin.consultations.index'),
    },
];

const selectedConsultation = ref<Consultation | null>(null);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

function openEditDialog(consultation: Consultation) {
    selectedConsultation.value = consultation;
    isEditDialogOpen.value = true;
}

function openDeleteDialog(consultation: Consultation) {
    selectedConsultation.value = consultation;
    isDeleteDialogOpen.value = true;
}

const q = ref(props.filters.q ?? '');

const filterConsultations = useDebounceFn(() => {
    router.get(route('admin.consultations.index'), { q: q.value }, { preserveState: true, replace: true });
}, 400);

function clearSearch() {
    q.value = '';
    filterConsultations();
}

watch([q], () => filterConsultations());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <form
                @submit.prevent="filterConsultations"
                class="mb-4"
            >
                <div class="relative flex h-9 items-center">
                    <Search class="absolute left-2 size-4 shrink-0 stroke-secondary-foreground" />

                    <Input
                        v-model="q"
                        name="q"
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
                            <TableHead>Age</TableHead>
                            <TableHead>Blood Pressure</TableHead>
                            <TableHead>Heart Rate</TableHead>
                            <TableHead v-if="hasAnyPermissionTo(['consultations:view', 'consultations:update', 'consultations:delete'])">
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(consultation, index) in consultations.data"
                            :key="consultation.id"
                        >
                            <TableCell>{{ (consultations.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{
                                    getFullName(
                                        consultation.appointment?.patient?.last_name!,
                                        consultation.appointment?.patient?.first_name!,
                                        consultation.appointment?.patient?.middle_name!,
                                    )
                                }}
                            </TableCell>

                            <TableCell>{{ consultation.appointment?.patient?.age.formatted_long }}</TableCell>

                            <TableCell>
                                {{ consultation.systolic && consultation.diastolic ? `${consultation?.systolic}/${consultation?.diastolic}` : 'N/A' }}
                            </TableCell>

                            <TableCell>{{ consultation?.heart_rate ? `${consultation?.heart_rate}bpm` : 'N/A' }}</TableCell>

                            <TableCell v-if="hasAnyPermissionTo(['consultations:view', 'consultations:update', 'consultations:delete'])">
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('consultations:view')"
                                        variant="info"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                route('admin.patients.appointments.consultations', {
                                                    patient: consultation?.appointment?.patient?.id,
                                                    appointment: consultation?.appointment?.id,
                                                })
                                            "
                                            prefetch
                                        >
                                            <Eye />
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('consultations:update')"
                                        @click="openEditDialog(consultation)"
                                        variant="warning"
                                        size="icon"
                                    >
                                        <Pencil />
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('consultations:delete')"
                                        @click="openDeleteDialog(consultation)"
                                        variant="destructive"
                                        size="icon"
                                    >
                                        <Trash />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!consultations.data.length"
                            :colspan="5"
                        >
                            No consultations.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <EditConsultationDialog
                    v-model:open="isEditDialogOpen"
                    :patient="selectedConsultation?.appointment?.patient"
                    :appointment="selectedConsultation?.appointment"
                    :consultation="selectedConsultation"
                />

                <DeleteConsultationDialog
                    v-model:open="isDeleteDialogOpen"
                    :consultation="selectedConsultation"
                />

                <Pagination :meta="consultations.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
