<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, School } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import ExamForm from '@/components/admin/ExamForm.vue';
import examRoutes from '@/routes/admin/exams';
import lessonRoutes from '@/routes/admin/lessons';
import classroomRoutes from '@/routes/admin/classrooms';

defineProps<{
    classrooms: { id: number; title: string }[];
    lessons: { id: number; title: string }[];
}>();
</script>

<template>
    <Head title="Buat Ujian" />

    <PageHeader title="Buat Ujian" description="Atur konfigurasi ujian baru.">
        <Button as-child variant="outline">
            <Link :href="examRoutes.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <EmptyState
        v-if="classrooms.length === 0 || lessons.length === 0"
        title="Data pendukung belum lengkap"
        description="Tambahkan minimal satu kelas dan satu mata pelajaran sebelum membuat ujian."
    >
        <div class="mt-2 flex gap-2">
            <Button as-child size="sm" variant="outline">
                <Link :href="classroomRoutes.index.url()">
                    <School class="size-4" />
                    Kelola Kelas
                </Link>
            </Button>
            <Button as-child size="sm" variant="outline">
                <Link :href="lessonRoutes.index.url()">
                    <BookOpen class="size-4" />
                    Kelola Mata Pelajaran
                </Link>
            </Button>
        </div>
    </EmptyState>

    <ExamForm
        v-else
        :action="examRoutes.store.url()"
        :classrooms="classrooms"
        :lessons="lessons"
        cancel-href="/admin/exams"
    />

    <Card v-if="classrooms.length > 0 && lessons.length > 0">
        <CardHeader>
            <CardTitle>Setelah ujian dibuat</CardTitle>
            <CardDescription>
                Tambahkan soal pilihan ganda, lalu buat sesi ujian dan daftarkan
                peserta.
            </CardDescription>
        </CardHeader>
        <CardContent class="text-sm text-muted-foreground">
            Ujian tanpa soal tidak dapat dikerjakan peserta. Setiap soal
            mendukung 2–5 pilihan jawaban.
        </CardContent>
    </Card>
</template>
