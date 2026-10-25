<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    BookOpen,
    ClipboardList,
    FileBarChart,
    GraduationCap,
    LayoutDashboard,
    LogOut,
    Menu,
    School,
    Users,
    X,
} from '@lucide/vue';
import type { PageProps } from '@/types';
import { dashboard } from '@/routes/admin';
import classrooms from '@/routes/admin/classrooms';
import lessons from '@/routes/admin/lessons';
import students from '@/routes/admin/students';
import exams from '@/routes/admin/exams';
import examSessions from '@/routes/admin/exam-sessions';
import reports from '@/routes/admin/reports';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

const page = usePage<PageProps>();
const open = ref(false);

const user = computed(() => page.props.auth.user);

const navigation = [
    { label: 'Dashboard', href: dashboard.url(), icon: LayoutDashboard },
    { label: 'Kelas', href: classrooms.index.url(), icon: School },
    { label: 'Mata Pelajaran', href: lessons.index.url(), icon: BookOpen },
    { label: 'Siswa', href: students.index.url(), icon: Users },
    { label: 'Ujian', href: exams.index.url(), icon: ClipboardList },
    {
        label: 'Sesi Ujian',
        href: examSessions.index.url(),
        icon: GraduationCap,
    },
    { label: 'Laporan', href: reports.index.url(), icon: FileBarChart },
];

function isActive(href: string): boolean {
    return page.url === href || page.url.startsWith(`${href}/`);
}
</script>

<template>
    <div class="min-h-screen bg-background lg:flex">
        <div
            v-if="open"
            class="fixed inset-0 z-30 bg-black/40 lg:hidden"
            @click="open = false"
        />

        <aside
            :class="
                cn(
                    'fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-sidebar-border bg-sidebar transition-transform lg:static lg:translate-x-0',
                    open ? 'translate-x-0' : '-translate-x-full',
                )
            "
        >
            <div
                class="flex h-16 items-center justify-between border-b border-sidebar-border px-4"
            >
                <Link href="/admin" class="flex items-center gap-2">
                    <span
                        class="grid size-8 place-items-center rounded-md bg-primary text-sm font-bold text-primary-foreground"
                        >E</span
                    >
                    <span class="font-semibold">Exam Admin</span>
                </Link>
                <button
                    class="text-muted-foreground lg:hidden"
                    aria-label="Tutup menu"
                    @click="open = false"
                >
                    <X class="size-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    :class="
                        cn(
                            'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                : 'text-sidebar-foreground hover:bg-sidebar-accent/60',
                        )
                    "
                    @click="open = false"
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="border-t border-sidebar-border p-3">
                <p class="truncate px-3 text-sm font-medium">
                    {{ user?.name }}
                </p>
                <p class="truncate px-3 text-xs text-muted-foreground">
                    {{ user?.email }}
                </p>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="mt-2 flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-accent-foreground"
                >
                    <LogOut class="size-4" />
                    Keluar
                </Link>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="flex h-16 items-center gap-3 border-b bg-card px-4 lg:px-8"
            >
                <Button
                    variant="ghost"
                    size="icon"
                    class="lg:hidden"
                    aria-label="Buka menu"
                    @click="open = true"
                >
                    <Menu class="size-5" />
                </Button>
                <p class="text-sm text-muted-foreground">Sistem Ujian Online</p>
            </header>

            <main class="mx-auto w-full max-w-6xl flex-1 space-y-6 p-4 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
