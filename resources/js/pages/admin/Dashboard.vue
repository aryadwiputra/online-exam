<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    ClipboardCheck,
    ClipboardList,
    GraduationCap,
    School,
    Users,
} from '@lucide/vue';
import students from '@/routes/admin/students';
import classrooms from '@/routes/admin/classrooms';
import exams from '@/routes/admin/exams';
import examSessions from '@/routes/admin/exam-sessions';
import reports from '@/routes/admin/reports';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    activeSessions: number;
    attempts: number;
    submitted: number;
}

interface DailyActivity {
    date: string;
    label: string;
    started: number;
    submitted: number;
    averageScore: number | null;
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

const props = defineProps<{
    stats: Stats;
    dailyActivity: DailyActivity[];
    activeSessions: ActiveSession[];
    recentAttempts: RecentAttempt[];
}>();

const kpis = computed(() => [
    {
        label: 'Total Siswa',
        value: props.stats.students,
        detail: 'akun peserta terdaftar',
        icon: Users,
        href: students.index.url(),
    },
    {
        label: 'Kelas',
        value: props.stats.classrooms,
        detail: 'kelas terdaftar',
        icon: School,
        href: classrooms.index.url(),
    },
    {
        label: 'Ujian',
        value: props.stats.exams,
        detail: 'ujian tersedia',
        icon: ClipboardList,
        href: exams.index.url(),
    },
    {
        label: 'Sesi Aktif',
        value: props.stats.activeSessions,
        detail: 'sedang berlangsung',
        icon: GraduationCap,
        href: examSessions.index.url(),
    },
    {
        label: 'Pengerjaan',
        value: props.stats.attempts,
        detail: 'total attempt peserta',
        icon: BookOpen,
        href: reports.index.url(),
    },
    {
        label: 'Terkumpul',
        value: props.stats.submitted,
        detail: 'ujian telah diselesaikan',
        icon: ClipboardCheck,
        href: reports.index.url(),
    },
]);

const maxDailyCount = computed(() =>
    Math.max(
        1,
        ...props.dailyActivity.map((day) =>
            Math.max(day.started, day.submitted),
        ),
    ),
);
const chartPoints = computed(() =>
    props.dailyActivity.map((day, index) => ({
        ...day,
        x:
            props.dailyActivity.length > 1
                ? 28 + (index * 544) / (props.dailyActivity.length - 1)
                : 300,
        y: day.averageScore === null ? 180 : 162 - day.averageScore * 1.35,
    })),
);
const averageScoreSegments = computed(() => {
    const segments: string[] = [];
    let currentSegment: string[] = [];

    for (const point of chartPoints.value) {
        if (point.averageScore === null) {
            if (currentSegment.length > 0) {
                segments.push(currentSegment.join(' '));
                currentSegment = [];
            }

            continue;
        }

        currentSegment.push(
            `${currentSegment.length === 0 ? 'M' : 'L'} ${point.x} ${point.y}`,
        );
    }

    if (currentSegment.length > 0) {
        segments.push(currentSegment.join(' '));
    }

    return segments;
});
</script>

<template>
    <Head title="Dashboard" />
    <PageHeader
        title="Dashboard"
        description="Ringkasan sistem ujian sekolah."
    />
    <FlashMessages />

    <section
        aria-label="Ringkasan administrasi"
        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
    >
        <Link
            v-for="item in kpis"
            :key="item.label"
            :href="item.href"
            class="group rounded-[12px] focus-visible:ring-[3px] focus-visible:ring-ring/30 focus-visible:outline-none"
        >
            <Card
                class="h-full transition-colors group-hover:border-primary/40"
            >
                <CardHeader
                    class="flex-row items-center justify-between gap-3 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                        >{{ item.label }}</CardTitle
                    >
                    <component
                        :is="item.icon"
                        class="size-4 text-muted-foreground transition-colors group-hover:text-primary"
                    />
                </CardHeader>
                <CardContent>
                    <p class="font-display text-3xl font-semibold tabular-nums">
                        {{ item.value }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ item.detail }}
                    </p>
                </CardContent>
            </Card>
        </Link>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Aktivitas pengerjaan</CardTitle>
                <p class="text-sm text-muted-foreground">
                    Attempt dimulai dan ujian dikumpulkan · 7 hari terakhir
                </p>
            </CardHeader>
            <CardContent>
                <div
                    v-if="
                        dailyActivity.some(
                            (day) => day.started || day.submitted,
                        )
                    "
                    class="overflow-x-auto"
                >
                    <div class="min-w-[520px]">
                        <div
                            class="mb-4 flex gap-5 text-xs text-muted-foreground"
                        >
                            <span class="inline-flex items-center gap-2"
                                ><span
                                    class="size-2.5 rounded-sm bg-primary"
                                />Dimulai</span
                            >
                            <span class="inline-flex items-center gap-2"
                                ><span
                                    class="size-2.5 rounded-sm bg-success"
                                />Dikumpulkan</span
                            >
                        </div>
                        <div
                            class="grid h-44 grid-cols-7 items-end gap-3 border-b border-border px-2"
                        >
                            <div
                                v-for="day in dailyActivity"
                                :key="day.date"
                                class="flex h-full flex-col items-center justify-end gap-2"
                            >
                                <div
                                    class="flex h-[calc(100%-1.5rem)] w-full items-end justify-center gap-1"
                                    :aria-label="`${day.label}: ${day.started} dimulai, ${day.submitted} dikumpulkan`"
                                >
                                    <span
                                        class="w-3 rounded-t-sm bg-primary"
                                        :style="{
                                            height: `${Math.max(day.started ? 6 : 0, (day.started / maxDailyCount) * 100)}%`,
                                        }"
                                    />
                                    <span
                                        class="w-3 rounded-t-sm bg-success"
                                        :style="{
                                            height: `${Math.max(day.submitted ? 6 : 0, (day.submitted / maxDailyCount) * 100)}%`,
                                        }"
                                    />
                                </div>
                                <span
                                    class="pb-2 text-[11px] text-muted-foreground"
                                    >{{ day.label }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <EmptyState
                    v-else
                    title="Belum ada aktivitas minggu ini"
                    description="Grafik akan terisi saat siswa mulai mengerjakan ujian."
                />
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Rata-rata nilai</CardTitle>
                <p class="text-sm text-muted-foreground">
                    Nilai dari ujian yang dikumpulkan · 7 hari terakhir
                </p>
            </CardHeader>
            <CardContent>
                <div
                    v-if="
                        dailyActivity.some((day) => day.averageScore !== null)
                    "
                    class="overflow-x-auto"
                >
                    <svg
                        class="h-52 w-full min-w-[520px] overflow-visible"
                        viewBox="0 0 600 210"
                        role="img"
                        aria-label="Grafik rata-rata nilai harian selama tujuh hari"
                    >
                        <line
                            v-for="tick in [0, 50, 100]"
                            :key="tick"
                            x1="28"
                            :y1="162 - tick * 1.35"
                            x2="572"
                            :y2="162 - tick * 1.35"
                            stroke="var(--border)"
                            stroke-dasharray="3 4"
                        />
                        <text
                            v-for="tick in [0, 50, 100]"
                            :key="`label-${tick}`"
                            x="0"
                            :y="166 - tick * 1.35"
                            fill="var(--muted-foreground)"
                            font-size="10"
                        >
                            {{ tick }}
                        </text>
                        <path
                            v-for="(segment, index) in averageScoreSegments"
                            :key="index"
                            :d="segment"
                            fill="none"
                            stroke="var(--primary)"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <g v-for="point in chartPoints" :key="point.date">
                            <circle
                                v-if="point.averageScore !== null"
                                :cx="point.x"
                                :cy="point.y"
                                r="4"
                                fill="var(--primary)"
                                stroke="var(--card)"
                                stroke-width="2"
                            >
                                <title>
                                    {{ point.label }}: rata-rata
                                    {{ point.averageScore }}
                                </title>
                            </circle>
                            <text
                                :x="point.x"
                                y="198"
                                text-anchor="middle"
                                fill="var(--muted-foreground)"
                                font-size="10"
                            >
                                {{ point.label }}
                            </text>
                        </g>
                    </svg>
                </div>
                <EmptyState
                    v-else
                    title="Belum ada nilai terkumpul minggu ini"
                    description="Nilai rata-rata muncul setelah siswa menyelesaikan ujian."
                />
            </CardContent>
        </Card>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <Card class="min-w-0">
            <CardHeader class="flex-row items-center justify-between gap-3">
                <div>
                    <CardTitle>Sesi ujian aktif</CardTitle>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Progres peserta pada sesi yang berlangsung.
                    </p>
                </div>
                <Link
                    :href="examSessions.index.url()"
                    class="text-sm font-medium text-primary hover:underline"
                    >Semua sesi</Link
                >
            </CardHeader>
            <CardContent>
                <EmptyState
                    v-if="activeSessions.length === 0"
                    title="Tidak ada sesi aktif"
                    description="Sesi yang sedang berlangsung akan tampil di sini."
                />
                <ul v-else class="divide-y divide-border">
                    <li
                        v-for="session in activeSessions"
                        :key="session.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ session.exam.title }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ session.title
                                }}<span v-if="session.exam.classroom?.title">
                                    · {{ session.exam.classroom.title }}</span
                                >
                            </p>
                        </div>
                        <Badge variant="secondary"
                            >{{ session.attempts_count }} /
                            {{ session.enrollments_count }} mulai</Badge
                        >
                    </li>
                </ul>
            </CardContent>
        </Card>

        <Card class="min-w-0">
            <CardHeader class="flex-row items-center justify-between gap-3">
                <div>
                    <CardTitle>Aktivitas terbaru</CardTitle>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Pengerjaan ujian terakhir diperbarui.
                    </p>
                </div>
                <Link
                    :href="reports.index.url()"
                    class="text-sm font-medium text-primary hover:underline"
                    >Semua hasil</Link
                >
            </CardHeader>
            <CardContent class="min-w-0">
                <EmptyState
                    v-if="recentAttempts.length === 0"
                    title="Belum ada pengerjaan"
                    description="Aktivitas peserta akan muncul setelah ujian dimulai."
                />
                <div v-else class="overflow-x-auto">
                    <Table>
                        <TableHeader
                            ><TableRow
                                ><TableHead>Peserta</TableHead
                                ><TableHead>Ujian</TableHead
                                ><TableHead>Status</TableHead
                                ><TableHead class="text-right"
                                    >Nilai</TableHead
                                ></TableRow
                            ></TableHeader
                        >
                        <TableBody>
                            <TableRow
                                v-for="attempt in recentAttempts"
                                :key="attempt.id"
                            >
                                <TableCell
                                    ><p class="font-medium">
                                        {{ attempt.student.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ attempt.student.nisn }}
                                    </p></TableCell
                                >
                                <TableCell class="text-sm">{{
                                    attempt.exam_session.exam?.title
                                }}</TableCell>
                                <TableCell
                                    ><Badge
                                        :variant="
                                            attempt.submitted_at
                                                ? 'success'
                                                : 'warning'
                                        "
                                        >{{
                                            attempt.submitted_at
                                                ? 'Selesai'
                                                : 'Berlangsung'
                                        }}</Badge
                                    ></TableCell
                                >
                                <TableCell class="text-right tabular-nums">{{
                                    attempt.score ?? '—'
                                }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </section>
</template>
