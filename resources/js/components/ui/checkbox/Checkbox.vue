<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { CheckboxIndicator, CheckboxRoot, type CheckboxRootEmits, type CheckboxRootProps } from 'reka-ui';
import { Check } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = defineProps<
    CheckboxRootProps & { class?: HTMLAttributes['class'] }
>();

const emits = defineEmits<CheckboxRootEmits>();
</script>

<template>
    <CheckboxRoot
        v-bind="props"
        :class="
            cn(
                'peer size-5 shrink-0 rounded-full border border-input bg-muted transition-colors outline-none',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/15',
                'data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary data-[state=checked]:rounded-full',
                'disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
        @update:model-value="emits('update:modelValue', $event)"
    >
        <CheckboxIndicator
            class="grid place-content-center text-current transition-none"
        >
            <Check class="size-3.5" />
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
