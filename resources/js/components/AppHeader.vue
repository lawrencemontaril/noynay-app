<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { usePermissions } from '@/composables/usePermissions';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Menu } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { hasRole } = usePermissions();

const page = usePage();
const auth = computed(() => page.props.auth);

const rightNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: hasRole('patient') ? route('dashboard') : route('admin.dashboard'),
        icon: LayoutGrid,
        access: !!page.props.auth.user,
    },
];

const filteredRightNavItems = computed(() => {
    return rightNavItems.filter((item) => item.access);
});
</script>

<template>
    <div>
        <div class="sticky top-0 z-50 border-b border-b-input bg-sidebar">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>

                        <SheetContent
                            side="left"
                            class="w-[300px] p-6"
                        >
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>

                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-16 fill-current text-black dark:text-white" />
                            </SheetHeader>

                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <div class="flex flex-col space-y-4">
                                    <Link
                                        v-for="item in filteredRightNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                        prefetch
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link
                    :href="route('home')"
                    class="flex items-center gap-x-2"
                >
                    <AppLogoIcon class="size-14" />
                    <span class="text-sm font-semibold text-white">Noynay Medical Center</span>
                </Link>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <div class="hidden space-x-1 lg:flex">
                            <template
                                v-for="item in filteredRightNavItems"
                                :key="item.title"
                            >
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="group h-9 w-9 cursor-pointer hover:bg-transparent"
                                                as-child
                                            >
                                                <Link
                                                    :href="item.href"
                                                    prefetch
                                                >
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component
                                                        :is="item.icon"
                                                        class="size-5 text-white opacity-80 group-hover:opacity-100"
                                                    />
                                                </Link>
                                            </Button>
                                        </TooltipTrigger>

                                        <TooltipContent :side-offset="12">
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <DropdownMenu v-if="auth.user">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 rounded-full"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage
                                        v-if="auth.user?.avatar"
                                        :src="auth.user.avatar"
                                        :alt="`${auth.user.first_name} ${auth.user.last_name}`"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(`${auth.user.first_name} ${auth.user.last_name}`) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent
                            align="end"
                            :side-offset="20"
                            class="w-56"
                        >
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div
                        v-else
                        class="flex items-center gap-2"
                    >
                        <Button
                            v-if="!route().current('login')"
                            variant="secondary"
                            as-child
                        >
                            <Link
                                :href="route('login')"
                                prefetch
                            >
                                Log in
                            </Link>
                        </Button>

                        <Button
                            v-if="!route().current('register')"
                            class="border border-primary-foreground dark:border-primary"
                            as-child
                        >
                            <Link
                                :href="route('register')"
                                prefetch
                            >
                                Register
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
