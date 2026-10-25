<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import {
    SelectContent,
    SelectPortal,
    SelectViewport,
    useForwardProps,
    type SelectContentProps,
} from 'reka-ui';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<
        SelectContentProps & { class?: HTMLAttributes['class'] }
    >(),
    {
        position: 'popper',
        sideOffset: 4,
    },
);

const forwarded = useForwardProps(props);
</script>

<template>
    <SelectPortal>
        <SelectContent
            v-bind="forwarded"
            :class="
                cn(
                    'relative z-50 max-h-(--reka-select-content-available-height) min-w-32 overflow-y-auto overflow-x-hidden rounded-md border bg-popover text-popover-foreground shadow-md',
                    'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
                    position === 'popper' &&
                        'data-[side=bottom]:translate-y-1 data-[side=top]:-translate-y-1',
                    props.class,
                )
            "
        >
            <SelectViewport class="p-1">
                <slot />
            </SelectViewport>
        </SelectContent>
    </SelectPortal>
</template>
