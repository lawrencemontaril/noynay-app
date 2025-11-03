<script setup lang="ts">
defineProps<{
    title: string;
    columns?: number;
}>();
</script>

<template>
    <div class="mb-3 space-y-3 rounded-xl border bg-muted/40 p-4 shadow-xs">
        <h3 class="border-b pb-1 text-xs font-semibold text-muted-foreground uppercase">
            {{ title }}
        </h3>

        <!-- Flexible grid -->
        <div
            v-if="columns"
            class="grid gap-4"
            :style="{
                'grid-template-columns': `repeat(${columns}, minmax(0, 1fr)`,
            }"
        >
            <slot />
        </div>

        <!-- Fallback (no grid) -->
        <div v-else>
            <slot />
        </div>
    </div>
</template>
