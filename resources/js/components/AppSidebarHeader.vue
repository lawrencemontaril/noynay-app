<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { computed } from 'vue';
import NotificationMenuContent from './NotificationMenuContent.vue';
import Button from './ui/button/Button.vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <header
        class="sticky top-0 z-25 flex h-16 shrink-0 items-center justify-between gap-2 border-b bg-background px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex items-center justify-between gap-4">
            <div id="sidebar-header"></div>

            <DropdownMenu>
                <DropdownMenuTrigger>
                    <div class="relative">
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-9"
                        >
                            <Bell />
                        </Button>

                        <div
                            v-if="auth.user.notifications?.length"
                            class="absolute -top-1 -right-2 flex h-4 w-fit items-center justify-center rounded-full bg-destructive p-1 text-[10px] leading-none font-semibold text-white"
                        >
                            <span
                                >{{ auth.user.notifications.length
                                }}{{ auth.user.notifications.length >= 10 ? '+' : '' }}</span
                            >
                        </div>
                    </div>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="end"
                    :side-offset="24"
                    class="max-w-svw p-0"
                >
                    <NotificationMenuContent :user="auth.user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
