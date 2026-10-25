<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import StudentForm from '@/components/admin/StudentForm.vue';
import students from '@/routes/admin/students';

defineProps<{
    classrooms: { id: number; title: string }[];
}>();
</script>

<template>
    <Head title="Tambah Siswa" />

    <PageHeader title="Tambah Siswa" description="Buat akun peserta ujian.">
        <Button as-child variant="outline">
            <Link :href="students.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <StudentForm
        :action="students.store.url()"
        :classrooms="classrooms"
        cancel-href="/admin/students"
    />
</template>
