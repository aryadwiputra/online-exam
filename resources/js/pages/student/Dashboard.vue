<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarClock, Clock, FileText, PlayCircle } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import EmptyState from '@/components/EmptyState.vue';
import studentRoutes from '@/routes/student';

interface Session {
    id: number;
    title: string;
    exam_title: string;
    lesson: string | null;
    description: string | null;
    duration: number;
    start_time: string;
    end_time: string;
    status: 'upcoming' | 'open' | 'ended';
    attempt: {
        id: number;
        submitted: boolean;
        score: string | null;
    } | null;
}

defineProps<{
    student: { id: number; name: string; nisn: string };
    classroom: { id: number; title: string } | null;
    sessions: Session[];
}>();

const statusLabel: Record<Session['status'], string> = {
    upcoming: 'Belum dimulai',
    open: 'Sedang berlangsung',
    ended: 'Sudah berakhir',
};

const statusVariant: Record<
    Session['status'],
    'secondary' | 'success' | 'outline'
> = {
    upcoming: 'secondary',
    open: 'success',
    ended: 'outline',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">
            Halo, {{ student.name }}
        </h1>
        <p class="text-sm text-muted-foreground">
            {{ classroom?.title }} · NISN {{ student.nisn }}
        </p>
    </div>

    <EmptyState
        v-if="sessions.length === 0"
        title="Belum ada ujian"
        description="Anda belum terdaftar pada sesi ujian mana pun. Hubungi pengawas jika ini keliru."
    />

    <div v-else class="space-y-3">
        <Card v-for="session in sessions" :key="session.id">
            <CardHeader class="flex-row items-start justify-between gap-4">
                <div class="space-y-1">
                    <CardTitle>{{ session.exam_title }}</CardTitle>
                    <CardDescription>
                        {{ session.title }}
                        <span v-if="session.lesson">
                            · {{ session.lesson }}</span
                        >
                    </CardDescription>
                </div>
                <Badge :variant="statusVariant[session.status]">
                    {{ statusLabel[session.status] }}
                </Badge>
            </CardHeader>
            <CardContent class="space-y-4">
                <p
                    v-if="session.description"
                    class="text-sm text-muted-foreground"
                >
                    {{ session.description }}
                </p>

                <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                    <span class="flex items-center gap-1.5">
                        <Clock class="size-4" />
                        {{ session.duration }} menit
                    </span>
                    <span class="flex items-center gap-1.5">
                        <CalendarClock class="size-4" />
                        {{ session.start_time }} — {{ session.end_time }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="session.attempt?.submitted">
                        <Button as-child>
                            <Link
                                :href="
                                    studentRoutes.attempts.result.url(
                                        session.attempt.id,
                                    )
                                "
                            >
                                <FileText class="size-4" />
                                Lihat Hasil ({{ session.attempt.score }})
                            </Link>
                        </Button>
                    </template>

                    <template v-else-if="session.attempt">
                        <Button as-child>
                            <Link
                                :href="
                                    studentRoutes.attempts.show.url(
                                        session.attempt.id,
                                    )
                                "
                            >
                                <PlayCircle class="size-4" />
                                Lanjutkan Ujian
                            </Link>
                        </Button>
                    </template>

                    <template v-else-if="session.status === 'open'">
                        <Button as-child>
                            <Link
                                :href="studentRoutes.exams.show.url(session.id)"
                            >
                                <PlayCircle class="size-4" />
                                Mulai Ujian
                            </Link>
                        </Button>
                    </template>

                    <p v-else class="text-sm text-muted-foreground">
                        {{
                            session.status === 'upcoming'
                                ? 'Ujian dapat dimulai sesuai jadwal.'
                                : 'Sesi ujian telah berakhir.'
                        }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
