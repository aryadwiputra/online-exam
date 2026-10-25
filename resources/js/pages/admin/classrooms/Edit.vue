<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import TitleForm from '@/components/admin/TitleForm.vue';
import classrooms from '@/routes/admin/classrooms';

defineProps<{
    classroom: { id: number; title: string };
}>();
</script>

<template>
    <Head title="Ubah Kelas" />

    <PageHeader title="Ubah Kelas" :description="classroom.title">
        <Button as-child variant="outline">
            <Link :href="classrooms.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <TitleForm
        :action="classrooms.update.url(classroom.id)"
        method="put"
        title="Perbarui"
        :initial-value="classroom.title"
        cancel-href="/admin/classrooms"
    />
</template>
