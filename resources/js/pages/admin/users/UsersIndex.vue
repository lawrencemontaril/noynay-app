<script setup lang="ts">
import Container from '@/components/Container.vue';
import CreateUserDialog from '@/components/CreateUserDialog.vue';
// import DeleteUserDialog from '@/components/DeleteUserDialog.vue';
import EditUserDialog from '@/components/EditUserDialog.vue';
import Pagination from '@/components/Pagination.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, PaginatedData, User } from '@/types';
import { USER_ROLES } from '@/types/constants';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Eye, Pencil, Search, Trash, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    users: PaginatedData<User>;
    filters: {
        q: string;
        role: string;
        is_active: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: route('admin.users.index'),
    },
];

const { hasPermissionTo, hasAnyPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

const selectedUser = ref<User | null>(null);
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

function openEditDialog(user: User) {
    selectedUser.value = user;
    isEditDialogOpen.value = true;
}

function openDeleteDialog(user: User) {
    selectedUser.value = user;
    isDeleteDialogOpen.value = true;
}

const q = ref(props.filters.q ?? '');
const role = ref(props.filters.role ?? 'all');
const is_active = ref(props.filters.is_active ?? 'all');

const filterUsers = useDebounceFn(() => {
    router.get(
        route('admin.users.index'),
        { q: q.value, role: role.value, is_active: is_active.value },
        { preserveState: true, replace: true },
    );
}, 400);

function clearSearch() {
    q.value = '';
    filterUsers();
}

watch([q, role, is_active], () => filterUsers());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Teleport
            v-if="hasPermissionTo('users:create')"
            defer
            to="#sidebar-header"
        >
            <Button @click="isCreateDialogOpen = true"> Create a user </Button>
        </Teleport>

        <Container>
            <form
                @submit.prevent="filterUsers"
                class="mb-4"
            >
                <div class="relative mb-4 flex h-9 items-center">
                    <Search class="absolute left-2 size-4 shrink-0 stroke-secondary-foreground" />

                    <Input
                        v-model="q"
                        name="q"
                        class="pl-8"
                        placeholder="Search for users"
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

            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Full Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>
                                <Select v-model="role">
                                    <SelectTrigger> Role: <SelectValue placeholder="Role" /> </SelectTrigger>

                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem
                                                v-for="role in USER_ROLES"
                                                :key="role.value"
                                                :value="role.value"
                                            >
                                                {{ role.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>
                            <TableHead>
                                <Select v-model="is_active">
                                    <SelectTrigger> Status: <SelectValue placeholder="Status" /> </SelectTrigger>

                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem value="true">Active</SelectItem>
                                            <SelectItem value="false">Inactive</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </TableHead>
                            <TableHead v-if="hasAnyPermissionTo(['users:update', 'users:delete'])">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="(user, index) in users.data"
                            :key="user.id"
                        >
                            <TableCell>{{ (users.meta.from_index ?? 0) + index }}</TableCell>

                            <TableCell>
                                {{ getFullName(user.last_name, user.first_name, user.middle_name) }}
                            </TableCell>

                            <TableCell>
                                {{ user.email }}
                            </TableCell>

                            <TableCell>{{ USER_ROLES.find((role) => role.value === user.role)?.label }}</TableCell>

                            <TableCell>
                                <Badge :variant="`${user.is_active ? 'default' : 'destructive'}`">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>

                            <TableCell
                                v-if="hasAnyPermissionTo(['users:update', 'users:delete'])"
                                class="flex items-center gap-2"
                            >
                                <Button
                                    v-if="hasPermissionTo('users:view')"
                                    variant="info"
                                    size="icon"
                                    as-child
                                >
                                    <Link
                                        :href="route('admin.users.show', user.id)"
                                        prefetch
                                    >
                                        <Eye />
                                    </Link>
                                </Button>

                                <Button
                                    v-if="hasPermissionTo('users:update')"
                                    @click="openEditDialog(user)"
                                    variant="warning"
                                    size="icon"
                                >
                                    <Pencil />
                                </Button>

                                <Button
                                    v-if="hasPermissionTo('users:delete')"
                                    @click="openDeleteDialog(user)"
                                    variant="destructive"
                                    size="icon"
                                >
                                    <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="!users.data.length"
                            :colspan="6"
                        >
                            No users.
                        </TableEmpty>
                    </TableBody>
                </Table>

                <CreateUserDialog v-model:open="isCreateDialogOpen" />

                <EditUserDialog
                    v-model:open="isEditDialogOpen"
                    :user="selectedUser"
                />

                <!-- <DeleteUserDialog
                    v-model:open="isDeleteDialogOpen"
                    :user="selectedUser"
                /> -->

                <Pagination :meta="users.meta" />
            </div>
        </Container>
    </AppLayout>
</template>
