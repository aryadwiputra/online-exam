<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const model = defineModel<string | number | null>();

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <input
        v-model="model"
        data-slot="input"
        :class="
            cn(
                'flex h-10 w-full min-w-0 rounded-[6px] border border-input bg-card px-3.5 py-2.5 text-sm transition-colors outline-none selection:bg-primary selection:text-primary-foreground file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/15',
                'aria-invalid:border-destructive aria-invalid:ring-destructive/20',
                props.class,
            )
        "
    />
</template>
