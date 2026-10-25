<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { SelectIcon, SelectTrigger, type SelectTriggerProps } from 'reka-ui';
import { ChevronDown } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = defineProps<
    SelectTriggerProps & { class?: HTMLAttributes['class'] }
>();
</script>

<template>
    <SelectTrigger
        :class="
            cn(
                'flex h-10 w-full items-center justify-between gap-2 rounded-[6px] border border-input bg-card px-3.5 py-2.5 text-sm whitespace-nowrap transition-colors outline-none',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/15',
                'disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive',
                '*:data-[slot=select-value]:line-clamp-1 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&>span]:truncate',
                props.class,
            )
        "
    >
        <slot />
        <SelectIcon as-child>
            <ChevronDown class="size-4 opacity-50" />
        </SelectIcon>
    </SelectTrigger>
</template>
