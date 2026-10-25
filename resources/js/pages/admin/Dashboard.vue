<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import FlashMessages from '@/components/FlashMessages.vue';

interface Stats {
    students: number;
    classrooms: number;
    exams: number;
    sessions: number;
    attempts: number;
    submitted: number;
}

interface ActiveSession {
    id: number;
    title: string;
    end_time: string;
    enrollments_count: number;
    attempts_count: number;
    exam: { title: string; classroom: { title: string } | null };
}

interface RecentAttempt {
    id: number;
    submitted_at: string | null;
    score: string | null;
    total_correct: number;
    student: { name: string; nisn: string };
    exam_session: { title: string; exam: { title: string } | null };
}

defineProps<{
    stats: Stats;
    activeSessions: ActiveSession[];
    recentAttempts: RecentAttempt[];
}>();

const cards = [
    { key: 'students', label: 'Siswa' },
    { key: 'classrooms', label: 'Kelas' },
    { key: 'exams', label: 'Ujian' },
    { key: 'sessions', label: 'Sesi Ujian' },
    { key: 'attempts', label: 'Pengerjaan' },
    { key: 'submitted', label: 'Sudah Dikumpulkan' },
] as const;
</script>

<template>
    <Head title="Dashboard" />

    <PageHeader
        title="Dashboard"
        description="Ringkasan aktivitas ujian online."
    />

    <FlashMessages />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card v-for="card in cards" :key="card.key" class="py-4">
            <CardContent class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ card.label }}</p>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ stats[card.key] }}
                </p>
            </CardContent>
        </Card>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Sesi berlangsung</CardTitle>
                <CardDescription
                    >Sesi yang sedang dibuka saat ini.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <EmptyState
                    v-if="activeSessions.length === 0"
                    title="Tidak ada sesi aktif"
                    description="Sesi ujian akan muncul di sini saat jadwalnya berlangsung."
                />
                <ul v-else class="space-y-3">
                    <li
                        v-for="session in activeSessions"
                        :key="session.id"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-lg border p-3"
                    >
                        <div class="space-y-0.5">
                            <p class="text-sm font-medium">
                                {{ session.exam.title }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ session.title }} ·
                                {{ session.exam.classroom?.title }} · selesai
                                {{ session.end_time }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Badge variant="secondary"
                                >{{ session.enrollments_count }} peserta</Badge
                            >
                            <Badge
                                >{{ session.attempts_count }} dikerjakan</Badge
                            >
                        </div>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Aktivitas terbaru</CardTitle>
                <CardDescription
                    >Pengerjaan ujian yang terakhir diperbarui.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <EmptyState
                    v-if="recentAttempts.length === 0"
                    title="Belum ada pengerjaan"
                    description="Data akan muncul setelah peserta mulai mengerjakan ujian."
                />
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Siswa</TableHead>
                            <TableHead>Ujian</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Nilai</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="attempt in recentAttempts"
                            :key="attempt.id"
                        >
                            <TableCell>
                                <p class="font-medium">
                                    {{ attempt.student.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ attempt.student.nisn }}
                                </p>
                            </TableCell>
                            <TableCell class="text-sm">
                                {{ attempt.exam_session.exam?.title }}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        attempt.submitted_at
                                            ? 'success'
                                            : 'warning'
                                    "
                                >
                                    {{
                                        attempt.submitted_at
                                            ? 'Selesai'
                                            : 'Berlangsung'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ attempt.score ?? '—' }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
