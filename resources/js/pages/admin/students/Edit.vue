<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import StudentForm from '@/components/admin/StudentForm.vue';
import students from '@/routes/admin/students';

defineProps<{
    student: {
        id: number;
        classroom_id: number;
        nisn: string;
        name: string;
        gender: 'L' | 'P';
    };
    classrooms: { id: number; title: string }[];
}>();
</script>

<template>
    <Head title="Ubah Siswa" />

    <PageHeader title="Ubah Siswa" :description="student.name">
        <Button as-child variant="outline">
            <Link :href="students.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <StudentForm
        :action="students.update.url(student.id)"
        method="put"
        :classrooms="classrooms"
        :student="student"
        cancel-href="/admin/students"
    />
</template>
