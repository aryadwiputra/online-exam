<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import ExamSessionForm from '@/components/admin/ExamSessionForm.vue';
import examSessions from '@/routes/admin/exam-sessions';

defineProps<{
    session: {
        id: number;
        exam_id: number;
        title: string;
        start_time: string;
        end_time: string;
    };
    exams: { id: number; title: string }[];
}>();
</script>

<template>
    <Head title="Ubah Sesi Ujian" />

    <PageHeader title="Ubah Sesi Ujian" :description="session.title">
        <Button as-child variant="outline">
            <Link :href="examSessions.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <ExamSessionForm
        :action="examSessions.update.url(session.id)"
        method="put"
        :exams="exams"
        :session="session"
        cancel-href="/admin/exam-sessions"
    />
</template>
