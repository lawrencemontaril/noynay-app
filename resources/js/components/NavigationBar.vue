<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { usePermissions } from '@/composables/usePermissions';
import { Link } from '@inertiajs/vue3';

const { hasRole } = usePermissions();
</script>

<template>
    <nav class="flex items-center justify-between gap-2 border-b border-secondary p-4">
        <h1>Noynay Medical Center</h1>

        <Button
            v-if="$page.props.auth.user"
            as-child
        >
            <Link :href="hasRole('patient') ? route('dashboard') : route('admin.dashboard')"> Dashboard </Link>
        </Button>
        <div
            v-else
            class="flex items-center gap-2"
        >
            <Button
                variant="outline"
                as-child
            >
                <Link :href="route('login')"> Log in </Link>
            </Button>

            <Button as-child>
                <Link :href="route('register')"> Register </Link>
            </Button>
        </div>
    </nav>
</template>
