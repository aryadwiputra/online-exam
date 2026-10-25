<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-muted/40 p-4">
        <div class="w-full max-w-md space-y-6">
            <div class="space-y-1 text-center">
                <span
                    class="mx-auto grid size-11 place-items-center rounded-xl bg-primary text-lg font-bold text-primary-foreground"
                    >E</span
                >
                <h1 class="text-xl font-semibold tracking-tight">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <slot />
        </div>
    </div>
</template>
