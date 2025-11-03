<script setup lang="ts">
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { router } from '@inertiajs/vue3';
import { Bell, CheckCircle2, RefreshCcw } from 'lucide-vue-next';
import { ref } from 'vue';
import Button from './ui/button/Button.vue';

defineProps<{
    user: User;
}>();

const isRefreshing = ref(false);

function markNotificationAsRead(id: string) {
    router.post(route('notifications.read', id));
}

function refreshNotifications() {
    isRefreshing.value = true;
    router.reload({
        only: ['auth'],
        onFinish: () => (isRefreshing.value = false),
    });
}
</script>

<template>
    <div class="min-w-[320px]">
        <!-- Header -->
        <DropdownMenuLabel class="flex items-center justify-between border-b border-border px-4 py-3 text-base font-semibold text-foreground">
            <div class="flex items-center gap-2">
                <Bell class="h-4 w-4 text-primary" />
                <span>Notifications</span>
            </div>

            <Button
                variant="outline"
                size="sm"
                @click="refreshNotifications"
                class="flex items-center gap-1 text-xs"
                :disabled="isRefreshing"
            >
                <RefreshCcw
                    :class="{ 'animate-spin': isRefreshing }"
                    class="h-3 w-3"
                />
                {{ isRefreshing ? 'Refreshing' : 'Refresh' }}
            </Button>
        </DropdownMenuLabel>

        <!-- Notifications -->
        <DropdownMenuGroup>
            <div
                v-if="user.notifications?.length"
                class="flex flex-col divide-y divide-border"
            >
                <DropdownMenuItem
                    v-for="notification in user.notifications"
                    :key="notification.id"
                    :as-child="true"
                    class="group rounded-none px-4 py-3 transition-colors hover:bg-muted/70"
                >
                    <button
                        class="flex w-full flex-col text-left focus:outline-none"
                        @click="markNotificationAsRead(notification.id)"
                    >
                        <div class="flex items-start justify-between">
                            <p class="line-clamp-2 text-sm font-medium text-foreground group-hover:text-primary">
                                {{ notification.message }}
                            </p>
                            <CheckCircle2
                                v-if="notification.read_at"
                                class="ml-2 h-4 w-4 shrink-0 text-muted-foreground"
                            />
                            <div
                                v-else
                                class="mt-1 ml-2 h-2 w-2 shrink-0 rounded-full bg-primary/80"
                            />
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ notification.created_at.human }}
                        </p>
                    </button>
                </DropdownMenuItem>
            </div>

            <div
                v-else
                class="px-4 py-6 text-center text-sm text-muted-foreground"
            >
                No new notifications.
            </div>
        </DropdownMenuGroup>
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
