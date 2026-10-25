<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Plus, Upload } from '@lucide/vue';
import type { Paginated } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import EmptyState from '@/components/EmptyState.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import exams from '@/routes/admin/exams';
import examQuestions from '@/routes/admin/exams/questions';
import questionRoutes from '@/routes/admin/questions';

interface Exam {
    id: number;
    title: string;
    duration: number;
    description: string | null;
    random_question: boolean;
    random_answer: boolean;
    show_answer: boolean;
    questions_count: number;
    sessions_count: number;
    lesson: { id: number; title: string } | null;
    classroom: { id: number; title: string } | null;
}

interface Question {
    id: number;
    question: string;
    options: string[];
    answer_index: number;
}

defineProps<{
    exam: Exam;
    questions: Paginated<Question>;
}>();

const labels = ['A', 'B', 'C', 'D', 'E'];
</script>

<template>
    <Head :title="exam.title" />

    <PageHeader
        :title="exam.title"
        :description="exam.description ?? undefined"
    >
        <Button as-child variant="outline">
            <Link :href="exams.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
        <Button as-child variant="outline">
            <Link :href="exams.edit.url(exam.id)">
                <Pencil class="size-4" />
                Ubah Ujian
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Kelas</p>
                <p class="font-medium">{{ exam.classroom?.title }}</p>
            </CardContent>
        </Card>
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Mata Pelajaran</p>
                <p class="font-medium">{{ exam.lesson?.title }}</p>
            </CardContent>
        </Card>
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Durasi</p>
                <p class="font-medium">{{ exam.duration }} menit</p>
            </CardContent>
        </Card>
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Sesi</p>
                <p class="font-medium">{{ exam.sessions_count }} sesi</p>
            </CardContent>
        </Card>
    </div>

    <Card>
        <CardHeader class="flex-row items-center justify-between">
            <div class="space-y-1">
                <CardTitle>Soal ({{ exam.questions_count }})</CardTitle>
                <CardDescription>
                    Pengacakan soal:
                    {{ exam.random_question ? 'aktif' : 'nonaktif' }} ·
                    Pengacakan jawaban:
                    {{ exam.random_answer ? 'aktif' : 'nonaktif' }} · Tampilkan
                    jawaban:
                    {{ exam.show_answer ? 'ya' : 'tidak' }}
                </CardDescription>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="outline" size="sm">
                    <Link :href="examQuestions.import.create.url(exam.id)">
                        <Upload class="size-4" />
                        Impor
                    </Link>
                </Button>
                <Button as-child size="sm">
                    <Link :href="examQuestions.create.url(exam.id)">
                        <Plus class="size-4" />
                        Tambah Soal
                    </Link>
                </Button>
            </div>
        </CardHeader>
        <CardContent class="space-y-4">
            <EmptyState
                v-if="questions.data.length === 0"
                title="Belum ada soal"
                description="Ujian tidak dapat dikerjakan sebelum memiliki soal."
            />

            <template v-else>
                <ol class="space-y-3">
                    <li
                        v-for="(question, index) in questions.data"
                        :key="question.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-medium">
                                {{ index + 1 }}. {{ question.question }}
                            </p>
                            <div class="flex shrink-0 gap-1">
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="
                                            questionRoutes.edit.url(question.id)
                                        "
                                        aria-label="Ubah soal"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteButton
                                    :href="
                                        questionRoutes.destroy.url(question.id)
                                    "
                                    title="Hapus soal ini?"
                                    description="Soal akan dihapus dari ujian ini."
                                />
                            </div>
                        </div>
                        <ul class="mt-2 space-y-1 text-sm">
                            <li
                                v-for="(
                                    option, optionIndex
                                ) in question.options"
                                :key="optionIndex"
                                class="flex gap-2"
                            >
                                <span class="font-mono text-muted-foreground"
                                    >{{ labels[optionIndex] }}.</span
                                >
                                <span
                                    :class="
                                        optionIndex === question.answer_index
                                            ? 'font-medium text-emerald-700'
                                            : ''
                                    "
                                >
                                    {{ option }}
                                </span>
                                <Badge
                                    v-if="optionIndex === question.answer_index"
                                    variant="success"
                                    >kunci</Badge
                                >
                            </li>
                        </ul>
                    </li>
                </ol>

                <Pagination
                    :links="questions.links"
                    :from="questions.from"
                    :to="questions.to"
                    :total="questions.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
