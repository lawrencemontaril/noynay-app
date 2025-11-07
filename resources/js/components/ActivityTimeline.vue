<script setup lang="ts">
import { useFormatters } from '@/composables/useFormatters';
import { Activity } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from './ui/button/Button.vue';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';

defineProps<{
    activities?: Activity[];
}>();

const { getFullName } = useFormatters();

const isLoading = ref(false);
const hasLoaded = ref(false);

const loadActivities = () => {
    router.reload({
        only: ['activities'],
        onBefore: () => (isLoading.value = true),
        onFinish: () => {
            isLoading.value = false;
            hasLoaded.value = true;
        },
    });
};
</script>

<template>
    <Card class="gap-0">
        <CardHeader class="flex items-center justify-between border-b">
            <CardTitle class="text-lg font-semibold">Activity Log</CardTitle>

            <Button
                v-if="!hasLoaded"
                @click="loadActivities"
                :disabled="isLoading"
                size="sm"
                variant="outline"
            >
                <span v-if="!isLoading">Load Logs</span>
                <span v-else>Loading...</span>
            </Button>
        </CardHeader>

        <CardContent class="py-0">
            <!-- Loading / Empty States -->
            <div
                v-if="isLoading && !hasLoaded"
                class="py-6 text-center text-sm text-muted-foreground"
            >
                Loading activity log...
            </div>

            <div
                v-else-if="activities?.length === 0 && hasLoaded"
                class="py-6 text-center text-sm text-muted-foreground"
            >
                No activity recorded yet.
            </div>

            <!-- Timeline -->
            <ul
                v-else
                class="relative space-y-8 border-l border-input pl-6"
            >
                <li
                    v-for="activity in activities"
                    :key="activity.id"
                    class="relative pt-3"
                >
                    <!-- Marker -->
                    <div
                        class="absolute top-4.5 -left-7.5 h-3 w-3 rounded-full bg-primary shadow ring-2 ring-background"
                    ></div>

                    <!-- Content -->
                    <div class="flex flex-col gap-2">
                        <!-- Header -->
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="leading-tight font-medium text-foreground">
                                    {{
                                        getFullName(
                                            activity.causer?.last_name!,
                                            activity.causer?.first_name!,
                                            activity.causer?.middle_name!,
                                        )
                                    }}
                                </p>
                                <span
                                    class="rounded-full border border-muted/30 bg-muted/50 px-2 py-0.5 text-xs text-muted-foreground capitalize"
                                >
                                    {{ activity.description || activity.event }}
                                </span>
                            </div>

                            <p class="text-xs whitespace-nowrap text-muted-foreground">
                                {{ activity.created_at?.formatted_date }}
                            </p>
                        </div>

                        <!-- Changes -->
                        <div
                            v-if="
                                Object.keys(activity.properties?.old || {}).length ||
                                Object.keys(activity.properties?.attributes || {}).length
                            "
                            class="flex flex-col gap-3 sm:flex-row sm:gap-4"
                        >
                            <div
                                v-if="Object.keys(activity.properties?.old || {}).length"
                                class="flex-1 rounded-lg bg-muted/30 p-3 text-xs"
                            >
                                <p class="mb-1 font-semibold text-foreground">Old Values</p>
                                <ul class="space-y-0.5">
                                    <li
                                        v-for="(value, key) in activity.properties.old"
                                        :key="key"
                                    >
                                        <strong>{{ key }}:</strong>
                                        <span class="ml-1 wrap-break-word text-muted-foreground">{{ value }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div
                                v-if="Object.keys(activity.properties?.attributes || {}).length"
                                class="flex-1 rounded-lg bg-muted/30 p-3 text-xs"
                            >
                                <p class="mb-1 font-semibold text-foreground">New Values</p>
                                <ul class="space-y-0.5">
                                    <li
                                        v-for="(value, key) in activity.properties.attributes"
                                        :key="key"
                                    >
                                        <strong>{{ key }}:</strong>
                                        <span class="ml-1 wrap-break-word text-muted-foreground">{{ value }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div
                            v-else
                            class="text-xs text-muted-foreground"
                        >
                            No attribute changes recorded.
                        </div>
                    </div>
                </li>
            </ul>
        </CardContent>
    </Card>
</template>
