<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import QuestionForm from '@/components/admin/QuestionForm.vue';
import exams from '@/routes/admin/exams';
import questions from '@/routes/admin/questions';

const props = defineProps<{
    exam: { id: number; title: string };
    question: {
        id: number;
        exam_id: number;
        question: string;
        options: string[];
        answer_index: number;
    };
}>();
</script>

<template>
    <Head title="Ubah Soal" />

    <PageHeader title="Ubah Soal" :description="exam.title">
        <Button as-child variant="outline">
            <Link :href="exams.show.url(exam.id)">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <QuestionForm
        :action="questions.update.url(question.id)"
        method="put"
        :exam-title="exam.title"
        :question="question"
        :cancel-href="exams.show.url(exam.id)"
    />
</template>
