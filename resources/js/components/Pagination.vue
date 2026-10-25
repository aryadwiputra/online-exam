<script setup lang="ts">
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

const props = defineProps<{
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}>();

function label(text: string): string {
    return text.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <div
        v-if="props.total > 0"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
    >
        <p class="text-sm text-muted-foreground">
            Menampilkan {{ props.from }}–{{ props.to }} dari {{ props.total }}
            data
        </p>

        <div class="flex flex-wrap items-center gap-1">
            <template v-for="(link, index) in props.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    :class="
                        cn(
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors',
                            link.active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'bg-background hover:bg-accent',
                        )
                    "
                    >{{ label(link.label) }}</Link
                >
                <span
                    v-else
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm text-muted-foreground opacity-50"
                    >{{ label(link.label) }}</span
                >
            </template>
        </div>
    </div>
</template>
