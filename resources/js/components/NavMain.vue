<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    groupLabel: string;
    items: NavItem[];
}>();

const page = usePage();

const filteredItems = computed(() => {
    return props.items.filter((item) => item.access);
});
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="z-5">{{ groupLabel }}</SidebarGroupLabel>
        <SidebarMenu class="z-10">
            <SidebarMenuItem
                v-for="item in filteredItems"
                :key="item.title"
            >
                <SidebarMenuButton
                    as-child
                    :is-active="item.href === page.url || item?.isActive"
                    :tooltip="item.title"
                >
                    <Link
                        :href="item.href"
                        prefetch
                    >
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
