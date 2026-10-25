<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import QuestionForm from '@/components/admin/QuestionForm.vue';
import exams from '@/routes/admin/exams';
import examQuestions from '@/routes/admin/exams/questions';

const props = defineProps<{
    exam: { id: number; title: string };
}>();
</script>

<template>
    <Head title="Tambah Soal" />

    <PageHeader title="Tambah Soal" :description="exam.title">
        <Button as-child variant="outline">
            <Link :href="exams.show.url(exam.id)">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <QuestionForm
        :action="examQuestions.store.url(exam.id)"
        :exam-title="exam.title"
        :cancel-href="exams.show.url(exam.id)"
    />
</template>
