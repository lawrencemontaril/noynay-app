<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';
import Skeleton from './ui/skeleton/Skeleton.vue';

defineProps<{
    userActiveStatusChart: any;
    userRegistrationsPerMonthChart: any;
    usersByRoleChart: any;
}>();
</script>

<template>
    <div class="my-4 grid min-h-96 grid-cols-1 gap-4 md:grid-cols-2">
        <div class="">
            <Deferred :data="['userActiveStatusChart']">
                <template #fallback>
                    <Skeleton class="h-full w-full" />
                </template>

                <div class="h-full w-full rounded-md border px-2 py-4">
                    <apexchart
                        :weight="userActiveStatusChart.weight"
                        :height="userActiveStatusChart.height"
                        :type="userActiveStatusChart.type"
                        :options="userActiveStatusChart.options"
                        :series="userActiveStatusChart.series"
                    ></apexchart>
                </div>
            </Deferred>
        </div>

        <div class="">
            <Deferred :data="['usersByRoleChart']">
                <template #fallback>
                    <Skeleton class="h-full w-full" />
                </template>

                <div class="h-full w-full rounded-md border px-2 py-4">
                    <apexchart
                        :weight="usersByRoleChart.weight"
                        :height="usersByRoleChart.height"
                        :type="usersByRoleChart.type"
                        :options="usersByRoleChart.options"
                        :series="usersByRoleChart.series"
                    ></apexchart>
                </div>
            </Deferred>
        </div>
    </div>

    <div>
        <Deferred :data="['userRegistrationsPerMonthChart']">
            <template #fallback>
                <Skeleton class="h-full w-full" />
            </template>

            <div class="h-full w-full rounded-md border px-2 py-4">
                <apexchart
                    :weight="userRegistrationsPerMonthChart.weight"
                    :height="userRegistrationsPerMonthChart.height"
                    :type="userRegistrationsPerMonthChart.type"
                    :options="userRegistrationsPerMonthChart.options"
                    :series="userRegistrationsPerMonthChart.series"
                ></apexchart>
            </div>
        </Deferred>
    </div>
</template>
