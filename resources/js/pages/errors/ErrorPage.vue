<script setup lang="ts">
import LandingLayout from '@/layouts/LandingLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    status: number;
}>();

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
    }[props.status];
});

const description = computed(() => {
    return {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our servers.',
        404: 'Sorry, the page you are looking for could not be found.',
        403: 'Sorry, you are forbidden from accessing this page.',
    }[props.status];
});
</script>

<template>
    <LandingLayout>
        <div>
            <h1>{{ title }}</h1>
            <div>{{ description }}</div>
        </div>
    </LandingLayout>
</template>
