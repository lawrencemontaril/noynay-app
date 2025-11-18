<script setup lang="ts">
import Container from '@/components/Container.vue';
import Pagination from '@/components/Pagination.vue';
import ShowConsultationDialog from '@/components/ShowConsultationDialog.vue';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Consultation, PaginatedData, Patient } from '@/types';
import { CONSULTATION_TYPES } from '@/types/constants';
import { useForm } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    patient: Patient;
    consultations: PaginatedData<Consultation>;
    filters: { type: string; status: string };
}>();

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consultations',
        href: route('consultations.index'),
    },
];

const selectedConsultation = ref<Consultation | null>(null);
const isShowDialogOpen = ref(false);

function openShowDialog(consultation: Consultation) {
    if (!consultation) return;
    selectedConsultation.value = consultation;
    isShowDialogOpen.value = true;
}

const inertiaForm = useForm({
    type: props.filters.type ?? 'all',
});

function filterConsultations() {
    inertiaForm.get(route('consultations.index'));
}

watch(() => [inertiaForm.type], filterConsultations);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container>
            <div class="rounded-lg border shadow-xs">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Consultation Date</TableHead>

                            <TableHead>
                                <Select v-model="inertiaForm.type">
                                    <SelectTrigger>
                                        Type:
                                        <SelectValue
                                            placeholder="Type"
                                            class="max-w-48 truncate"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="type in CONSULTATION_TYPES"
                                                :key="type.value"
                                                :value="type.value"
                                            >
                                                {{ type.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>

                            <TableHead>Blood Pressure</TableHead>
                            <TableHead>Heart Rate</TableHead>
                            <TableHead
                                v-if="
                                    hasAnyPermissionTo([
                                        'consultations:view',
                                        'consultations:update',
                                        'consultations:delete',
                                    ])
                                "
                            >
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
                                {{ consultation.created_at.formatted_date }}
                            </TableCell>

                            <TableCell>
                                {{ CONSULTATION_TYPES.find((type) => type.value === consultation.type)?.label }}
                            </TableCell>

                            <TableCell>
                                {{
                                    consultation.systolic && consultation.diastolic
                                        ? `${consultation?.systolic}/${consultation?.diastolic}`
                                        : 'N/A'
                                }}
                            </TableCell>

                            <TableCell>{{
                                consultation?.heart_rate ? `${consultation?.heart_rate}bpm` : 'N/A'
                            }}</TableCell>

                            <TableCell
                                v-if="
                                    hasAnyPermissionTo([
                                        'consultations:view',
                                        'consultations:update',
                                        'consultations:delete',
                                    ])
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="hasPermissionTo('consultations:view')"
                                        @click="openShowDialog(consultation)"
                                        variant="info"
                                        size="icon"
                                    >
                                        <Eye />
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

                <ShowConsultationDialog
                    v-model:open="isShowDialogOpen"
                    :patient="patient"
                    :consultation="selectedConsultation"
                />

                <Pagination :meta="consultations.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
