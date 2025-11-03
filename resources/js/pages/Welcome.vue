<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />

    <LandingLayout>
        <AuthBase
            title="We serve to make you better"
            description="We are open 24/7"
        >
            <Button
                size="lg"
                as-child
            >
                <Link :href="route('register')"> Get started </Link>
            </Button>
        </AuthBase>
    </LandingLayout>
</template>
