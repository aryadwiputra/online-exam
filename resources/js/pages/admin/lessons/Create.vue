<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import TitleForm from '@/components/admin/TitleForm.vue';
import lessons from '@/routes/admin/lessons';
</script>

<template>
    <Head title="Tambah Mata Pelajaran" />

    <PageHeader
        title="Tambah Mata Pelajaran"
        description="Buat mata pelajaran baru."
    >
        <Button as-child variant="outline">
            <Link :href="lessons.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <TitleForm
        :action="lessons.store.url()"
        title="Simpan"
        cancel-href="/admin/lessons"
    />
</template>
