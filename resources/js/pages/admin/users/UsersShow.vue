<script setup lang="ts">
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import Container from '@/components/Container.vue';
import DataCard from '@/components/DataCard.vue';
import EditUserDialog from '@/components/EditUserDialog.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { getInitials } from '@/composables/useInitials';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Activity, BreadcrumbItem, User } from '@/types';
import { Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    user: User;
    activities?: Activity[];
}>();

const { getFullName } = useFormatters();
const { hasPermissionTo } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: route('admin.users.index'),
    },
    {
        title: getFullName(props.user.last_name, props.user.first_name, props.user.middle_name),
        href: route('admin.users.show', props.user.id),
    },
];

const isEditUserDialogOpen = ref(false);

function openEditUserDialog() {
    isEditUserDialogOpen.value = true;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Container class="bg-card p-6">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-5">
                    <Avatar class="size-24 rounded-xl border border-foreground text-4xl shadow-sm">
                        <AvatarFallback class="rounded-xl font-semibold text-black dark:text-white">
                            {{ getInitials(`${user.first_name} ${user.last_name}`) }}
                        </AvatarFallback>
                    </Avatar>

                    <div>
                        <h1 class="text-2xl leading-tight font-semibold">
                            {{ getFullName(user.last_name, user.first_name, user.middle_name) }}
                        </h1>
                    </div>
                </div>
            </div>
        </Container>

        <Container class="space-y-4">
            <div class="space-y-4 rounded-xl border bg-muted/40 p-6 shadow-sm">
                <!-- Header / Edit button -->
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold tracking-tight">User Information</h2>
                    <Button
                        v-if="hasPermissionTo('users:update')"
                        @click="openEditUserDialog"
                        variant="warning"
                        size="icon"
                        class="flex items-center gap-2"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Personal Information -->
                <DataCard
                    title="Personal Details"
                    :columns="4"
                >
                    <div>
                        <label class="text-xs font-medium text-muted-foreground">First Name</label>
                        <p class="text-sm font-medium">{{ user.first_name }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Last Name</label>
                        <p class="text-sm font-medium">{{ user.last_name }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Middle Name</label>
                        <p class="text-sm font-medium">{{ user.middle_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Email Address</label>
                        <p class="text-sm font-medium">{{ user.email }}</p>
                    </div>
                </DataCard>

                <DataCard title="Account Status">
                    <p class="text-sm">{{ user.is_active ? 'Active' : 'Inactive' }}</p>
                </DataCard>
            </div>

            <ActivityTimeline :activities="activities" />

            <EditUserDialog
                v-model:open="isEditUserDialogOpen"
                :user="user"
            />
        </Container>
    </AppLayout>
</template>
