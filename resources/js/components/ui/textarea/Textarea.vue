<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const model = defineModel<string | number | null>();

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <textarea
        v-model="model"
        data-slot="textarea"
        :class="
            cn(
                'flex min-h-16 w-full rounded-[6px] border border-input bg-card px-3.5 py-2.5 text-sm transition-colors outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/15',
                'aria-invalid:border-destructive aria-invalid:ring-destructive/20',
                props.class,
            )
        "
    />
</template>
