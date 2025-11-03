<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

const flash = ref<any>({
    success: page.props.flash?.success || null,
    error: page.props.flash?.error || null,
    info: page.props.flash?.info || null,
});
</script>

<template>
    <AppShell class="relative flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <Transition
            name="slide-up"
            mode="out-in"
        >
            <div
                v-if="flash.success"
                class="bg-primary px-4 py-2 text-sm text-primary-foreground shadow"
            >
                {{ flash.success }}
            </div>
        </Transition>

        <Transition
            name="slide-up"
            mode="out-in"
        >
            <div
                v-if="flash.error"
                class="bg-destructive px-4 py-2 text-sm text-destructive-foreground shadow"
            >
                {{ flash.error }}
            </div>
        </Transition>

        <Transition
            name="slide-up"
            mode="out-in"
        >
            <div
                v-if="flash.info"
                class="bg-sky-500 px-4 py-2 text-sm text-sky-50 shadow"
            >
                {{ flash.info }}
            </div>
        </Transition>

        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
