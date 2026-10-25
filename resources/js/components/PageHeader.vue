<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-wrap items-start justify-between gap-4 border-b border-border pb-4"
    >
        <div class="space-y-1">
            <h1 class="font-display text-2xl font-semibold tracking-tight">
                {{ title }}
            </h1>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <slot />
        </div>
    </div>
</template>
