<script setup lang="ts">
import Container from '@/components/Container.vue';
import CreatePatientDialog from '@/components/CreatePatientDialog.vue';
import DeletePatientDialog from '@/components/DeletePatientDialog.vue';
import EditPatientDialog from '@/components/EditPatientDialog.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, PaginatedData, Patient } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Eye, Pencil, Search, Trash, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    patients: PaginatedData<Patient>;
    filters: {
        q: string;
        gender: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: route('admin.patients.index'),
    },
];

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const selectedPatient = ref<Patient | null>(null);
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

function openCreateDialog() {
    isCreateDialogOpen.value = true;
}

function openEditDialog(patient: Patient) {
    if (!patient) return;
    selectedPatient.value = patient;
    isEditDialogOpen.value = true;
}

function openDeleteDialog(patient: Patient) {
    if (!patient) return;
    selectedPatient.value = patient;
    isDeleteDialogOpen.value = true;
}

const q = ref(props.filters.q ?? '');
const gender = ref(props.filters.gender ?? 'all');

const filterPatients = useDebounceFn(() => {
    router.get(
        route('admin.patients.index'),
        { q: q.value, gender: gender.value },
        { preserveState: true, replace: true },
    );
}, 400);

function clearSearch() {
    q.value = '';
    filterPatients();
}

watch([q, gender], () => filterPatients());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Teleport
            v-if="hasPermissionTo('patients:create')"
            defer
            to="#sidebar-header"
        >
            <Button @click="openCreateDialog">Create a patient</Button>
        </Teleport>

        <Container>
            <form
                @submit.prevent="filterPatients"
                class="mb-4"
            >
                <div class="relative mb-4 flex h-9 items-center">
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
                            <TableHead>Full Name</TableHead>
                            <TableHead>
                                <Select v-model="gender">
                                    <SelectTrigger> Gender: <SelectValue placeholder="Gender" /> </SelectTrigger>

                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem value="male">Male</SelectItem>
                                            <SelectItem value="female">Female</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>
                            <TableHead>Age</TableHead>
                            <TableHead v-if="hasAnyPermissionTo(['patients:update', 'patients:delete'])"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(patient, index) in patients.data"
                            :key="patient.id"
                        >
                            <TableCell>{{ (patients.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{ patient.last_name }}, {{ patient.first_name }}
                                {{ patient.middle_name ? `${patient.middle_name[0]}.` : '' }}
                            </TableCell>

                            <TableCell class="capitalize">
                                {{ patient.gender }}
                            </TableCell>

                            <TableCell>
                                {{ patient.age.formatted_short }}
                            </TableCell>

                            <TableCell
                                v-if="hasAnyPermissionTo(['patients:view', 'patients:update', 'patients:delete'])"
                            >
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('patients:view')"
                                        variant="info"
                                        size="icon"
                                        as-child
                                    >
                                        <Link :href="route('admin.patients.show', patient.id)">
                                            <Eye />
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('patients:update')"
                                        @click="openEditDialog(patient)"
                                        variant="warning"
                                        size="icon"
                                    >
                                        <Pencil />
                                    </Button>

                                    <Button
                                        v-if="hasPermissionTo('patients:delete')"
                                        @click="openDeleteDialog(patient)"
                                        variant="destructive"
                                        size="icon"
                                    >
                                        <Trash />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!patients.data.length"
                            :colspan="5"
                        >
                            No patients.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <CreatePatientDialog v-model:open="isCreateDialogOpen" />

                <EditPatientDialog
                    v-model:open="isEditDialogOpen"
                    :patient="selectedPatient"
                />

                <DeletePatientDialog
                    v-model:open="isDeleteDialogOpen"
                    :patient="selectedPatient"
                />

                <Pagination :meta="patients.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
