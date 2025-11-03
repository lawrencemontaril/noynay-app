<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

watch(
    () => page.props.flash,
    (newFlash) => {
        flash.value = { ...newFlash };

        if (newFlash?.success || newFlash?.error || newFlash?.info) {
            setTimeout(() => {
                flash.value = { success: null, error: null, info: null };
            }, 5000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbs"
        class="relative space-y-2"
    >
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

        <slot />
    </AppLayout>
</template>

<style scoped>
.slide-up-leave-active {
    transition: all 0.5s ease;
}

/* animate both vertical movement and height collapse */
.slide-up-leave-to {
    transform: translateY(-36px);
    max-height: 0;
    margin: 0;
    padding-top: 0;
    padding-bottom: 0;
    overflow: hidden;
}
</style>
