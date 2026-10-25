<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import TitleForm from '@/components/admin/TitleForm.vue';
import classrooms from '@/routes/admin/classrooms';
</script>

<template>
    <Head title="Tambah Kelas" />

    <PageHeader title="Tambah Kelas" description="Buat kelas baru.">
        <Button as-child variant="outline">
            <Link :href="classrooms.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <TitleForm
        :action="classrooms.store.url()"
        title="Simpan"
        cancel-href="/admin/classrooms"
    />
</template>
