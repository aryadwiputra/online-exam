<script setup lang="ts">
import { Inbox } from '@lucide/vue';

defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed px-6 py-12 text-center"
    >
        <Inbox class="size-8 text-muted-foreground" />
        <p class="font-medium">{{ title }}</p>
        <p v-if="description" class="max-w-sm text-sm text-muted-foreground">
            {{ description }}
        </p>
        <slot />
    </div>
</template>
