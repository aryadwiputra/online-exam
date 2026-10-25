<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>

<template>
    <div
        data-slot="card"
        :class="
            cn(
                'flex flex-col gap-5 overflow-hidden rounded-[12px] border bg-card py-5 text-card-foreground',
                props.class,
            )
        "
    >
        <slot />
    </div>
</template>
