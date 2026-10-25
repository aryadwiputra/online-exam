<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import TitleForm from '@/components/admin/TitleForm.vue';
import lessons from '@/routes/admin/lessons';

defineProps<{
    lesson: { id: number; title: string };
}>();
</script>

<template>
    <Head title="Ubah Mata Pelajaran" />

    <PageHeader title="Ubah Mata Pelajaran" :description="lesson.title">
        <Button as-child variant="outline">
            <Link :href="lessons.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <TitleForm
        :action="lessons.update.url(lesson.id)"
        method="put"
        title="Perbarui"
        :initial-value="lesson.title"
        cancel-href="/admin/lessons"
    />
</template>
