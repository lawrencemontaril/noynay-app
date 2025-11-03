<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { cn } from '@/lib/utils';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid h-[calc(100dvh-4rem-1px)] flex-col items-center justify-center sm:px-0 lg:max-w-none lg:grid-cols-12 lg:px-0">
        <div
            :class="
                cn(
                    'relative col-span-0 hidden h-full flex-col bg-muted p-10 text-white lg:col-span-6 lg:flex dark:border-r',
                    route().current('register') && 'lg:col-span-4',
                )
            "
        >
            <div class="absolute inset-0 bg-background" />
            <div class="relative z-20 m-auto">
                <AppLogoIcon class="w-[384px]" />
            </div>
        </div>
        <div :class="cn('col-span-6 p-6 lg:p-4', route().current('register') && 'lg:col-span-8')">
            <div
                :class="
                    cn(
                        'mx-auto flex w-full flex-col justify-center space-y-6 py-4 sm:w-[350px]',
                        route().current('register') && 'col-span-8 sm:w-full',
                    )
                "
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1
                        class="text-xl font-medium tracking-tight"
                        v-if="title"
                    >
                        {{ title }}
                    </h1>
                    <p
                        class="text-sm text-muted-foreground"
                        v-if="description"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
