<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import ExamSessionForm from '@/components/admin/ExamSessionForm.vue';
import examSessions from '@/routes/admin/exam-sessions';

defineProps<{
    exams: { id: number; title: string }[];
}>();
</script>

<template>
    <Head title="Buat Sesi Ujian" />

    <PageHeader
        title="Buat Sesi Ujian"
        description="Tentukan jadwal pelaksanaan ujian."
    >
        <Button as-child variant="outline">
            <Link :href="examSessions.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <ExamSessionForm
        :action="examSessions.store.url()"
        :exams="exams"
        cancel-href="/admin/exam-sessions"
    />
</template>
