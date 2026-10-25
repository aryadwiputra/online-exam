<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import ExamForm from '@/components/admin/ExamForm.vue';
import exams from '@/routes/admin/exams';

defineProps<{
    exam: {
        id: number;
        lesson_id: number;
        classroom_id: number;
        title: string;
        duration: number;
        description: string | null;
        random_question: boolean;
        random_answer: boolean;
        show_answer: boolean;
    };
    classrooms: { id: number; title: string }[];
    lessons: { id: number; title: string }[];
}>();
</script>

<template>
    <Head title="Ubah Ujian" />

    <PageHeader title="Ubah Ujian" :description="exam.title">
        <Button as-child variant="outline">
            <Link :href="exams.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <ExamForm
        :action="exams.update.url(exam.id)"
        method="put"
        :classrooms="classrooms"
        :lessons="lessons"
        :exam="exam"
        cancel-href="/admin/exams"
    />
</template>
