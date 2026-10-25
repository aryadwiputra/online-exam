<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { LogOut } from '@lucide/vue';
import type { PageProps } from '@/types';
import { Button } from '@/components/ui/button';

const page = usePage<PageProps>();
const student = computed(() => page.props.auth.student);
</script>

<template>
    <div class="min-h-screen bg-background">
        <header class="border-b bg-card">
            <div
                class="mx-auto flex h-16 max-w-4xl items-center justify-between gap-4 px-4"
            >
                <Link
                    href="/student/dashboard"
                    class="flex items-center gap-2 font-semibold"
                >
                    <span
                        class="grid size-8 place-items-center rounded-md bg-primary text-sm font-bold text-primary-foreground"
                        >E</span
                    >
                    Ujian Online
                </Link>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm leading-tight font-medium">
                            {{ student?.name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            NISN {{ student?.nisn }}
                        </p>
                    </div>
                    <Button as-child variant="ghost" size="icon">
                        <Link href="/student/logout" method="post" as="button">
                            <LogOut class="size-4" />
                            <span class="sr-only">Keluar</span>
                        </Link>
                    </Button>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-4xl space-y-6 p-4 lg:p-8">
            <slot />
        </main>
    </div>
</template>
