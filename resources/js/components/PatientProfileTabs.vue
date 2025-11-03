<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { useFormatters } from '@/composables/useFormatters';
import { getInitials } from '@/composables/useInitials';
import { usePermissions } from '@/composables/usePermissions';
import { cn } from '@/lib/utils';
import { Patient } from '@/types';
import { Link } from '@inertiajs/vue3';
import Container from './Container.vue';
import Avatar from './ui/avatar/Avatar.vue';
import AvatarFallback from './ui/avatar/AvatarFallback.vue';

defineProps<{
    patient: Patient;
}>();

const { hasPermissionTo } = usePermissions();
const { getFullName } = useFormatters();

const isActive = (url: string, params?: object | number) => {
    return route().current(url, params);
};
</script>

<template>
    <header>
        <!-- Patient Banner -->
        <Container class="bg-card p-6">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-5">
                    <Avatar
                        :class="
                            cn(
                                route().current('admin.patients.show') ? 'size-24 text-4xl' : 'size-12 text-xl',
                                'rounded-xl border border-foreground shadow-sm',
                            )
                        "
                    >
                        <AvatarFallback class="rounded-xl font-semibold text-black dark:text-white">
                            {{ getInitials(`${patient.first_name} ${patient.last_name}`) }}
                        </AvatarFallback>
                    </Avatar>

                    <div>
                        <h1 class="text-2xl leading-tight font-semibold">
                            {{ getFullName(patient.last_name, patient.first_name, patient.middle_name) }}
                        </h1>
                    </div>
                </div>
            </div>
        </Container>

        <!-- Tabs -->
        <div class="grid grid-cols-2 border-b">
            <Button
                v-if="hasPermissionTo('patients:view')"
                :variant="isActive('admin.patients.show') ? 'secondary' : 'ghost'"
                class="rounded-none border-b-2 px-6 py-3 font-medium transition-all duration-150"
                :class="
                    isActive('admin.patients.show')
                        ? 'border-primary text-primary'
                        : 'border-transparent hover:border-muted'
                "
                :disabled="isActive('admin.patients.show')"
                as-child
            >
                <Link
                    :href="route('admin.patients.show', patient.id)"
                    prefetch
                >
                    Patient Information
                </Link>
            </Button>

            <Button
                v-if="hasPermissionTo('appointments:view')"
                :variant="
                    isActive('admin.patients.appointments') ||
                    isActive('admin.patients.appointments.*', { patient: patient.id })
                        ? 'secondary'
                        : 'ghost'
                "
                class="rounded-none border-b-2 px-6 py-3 font-medium transition-all duration-150"
                :class="
                    isActive('admin.patients.appointments') ||
                    isActive('admin.patients.appointments.*', { patient: patient.id })
                        ? 'border-primary text-primary'
                        : 'border-transparent hover:border-muted'
                "
                :disabled="
                    isActive('admin.patients.appointments') ||
                    isActive('admin.patients.appointments.*', { patient: patient.id })
                "
                as-child
            >
                <Link
                    :href="route('admin.patients.appointments', patient.id)"
                    prefetch
                >
                    Appointment History
                </Link>
            </Button>
        </div>
    </header>
</template>
