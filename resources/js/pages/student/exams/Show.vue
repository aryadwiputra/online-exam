<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CalendarClock, Clock, ListChecks } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import studentRoutes from '@/routes/student';

const props = defineProps<{
    session: {
        id: number;
        title: string;
        start_time: string;
        end_time: string;
        status: 'upcoming' | 'open' | 'ended';
    };
    exam: {
        id: number;
        title: string;
        description: string | null;
        duration: number;
        lesson: string | null;
        questions_count: number;
    };
}>();

const form = useForm<Record<string, string>>({});

function start(): void {
    form.post(studentRoutes.exams.start.url(props.session.id));
}
</script>

<template>
    <Head :title="exam.title" />

    <div class="space-y-1">
        <Link
            href="/student/dashboard"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-3.5" />
            Kembali ke dashboard
        </Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ exam.title }}</h1>
        <p class="text-sm text-muted-foreground">
            {{ session.title }}
            <span v-if="exam.lesson"> · {{ exam.lesson }}</span>
        </p>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>Konfirmasi Ujian</CardTitle>
            <CardDescription>
                Pastikan Anda siap sebelum memulai. Waktu berjalan otomatis
                setelah ujian dimulai.
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
            <p v-if="exam.description" class="text-sm">
                {{ exam.description }}
            </p>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border p-3">
                    <p
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Clock class="size-3.5" /> Durasi
                    </p>
                    <p class="font-medium">{{ exam.duration }} menit</p>
                </div>
                <div class="rounded-lg border p-3">
                    <p
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <ListChecks class="size-3.5" /> Jumlah soal
                    </p>
                    <p class="font-medium">{{ exam.questions_count }} soal</p>
                </div>
                <div class="rounded-lg border p-3">
                    <p
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <CalendarClock class="size-3.5" /> Jadwal sesi
                    </p>
                    <p class="text-xs font-medium">
                        {{ session.start_time }} — {{ session.end_time }}
                    </p>
                </div>
            </div>

            <Alert v-if="session.status !== 'open'" variant="destructive">
                <AlertTitle>Ujian belum dapat dimulai</AlertTitle>
                <AlertDescription>
                    {{
                        session.status === 'upcoming'
                            ? 'Sesi ujian belum dimulai. Silakan kembali sesuai jadwal.'
                            : 'Sesi ujian sudah berakhir.'
                    }}
                </AlertDescription>
            </Alert>

            <Alert v-else>
                <AlertTitle>Sebelum memulai</AlertTitle>
                <AlertDescription>
                    Jawaban tersimpan otomatis saat dipilih. Ujian akan
                    terkumpul otomatis saat waktu habis.
                </AlertDescription>
            </Alert>

            <p v-if="form.errors.session" class="text-sm text-destructive">
                {{ form.errors.session }}
            </p>

            <div class="flex gap-2">
                <Button
                    :disabled="
                        session.status !== 'open' ||
                        form.processing ||
                        exam.questions_count === 0
                    "
                    @click="start"
                >
                    Mulai Ujian
                </Button>
                <Badge v-if="exam.questions_count === 0" variant="destructive">
                    Ujian belum memiliki soal
                </Badge>
            </div>
        </CardContent>
    </Card>
</template>
