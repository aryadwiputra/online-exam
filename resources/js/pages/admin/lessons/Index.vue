<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Pencil, Plus } from '@lucide/vue';
import type { Paginated } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import EmptyState from '@/components/EmptyState.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import lessonRoutes from '@/routes/admin/lessons';

interface Lesson {
    id: number;
    title: string;
    exams_count: number;
}

const props = defineProps<{
    lessons: Paginated<Lesson>;
    filters: { search: string };
}>();

const search = ref(props.filters.search);

watch(search, (value) => {
    window.setTimeout(() => {
        router.get(
            lessonRoutes.index.url(),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="Mata Pelajaran" />

    <PageHeader
        title="Mata Pelajaran"
        description="Kelola daftar mata pelajaran."
    >
        <Button as-child>
            <Link :href="lessonRoutes.create.url()">
                <Plus class="size-4" />
                Tambah Mata Pelajaran
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <Card>
        <CardContent class="space-y-4">
            <div class="max-w-sm">
                <Input v-model="search" placeholder="Cari mata pelajaran..." />
            </div>

            <EmptyState
                v-if="lessons.data.length === 0"
                title="Belum ada mata pelajaran"
                description="Tambahkan mata pelajaran untuk membuat ujian."
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama</TableHead>
                            <TableHead>Jumlah Ujian</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="lesson in lessons.data"
                            :key="lesson.id"
                        >
                            <TableCell class="font-medium">
                                {{ lesson.title }}
                            </TableCell>
                            <TableCell>{{ lesson.exams_count }}</TableCell>
                            <TableCell class="flex justify-end gap-1">
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="lessonRoutes.edit.url(lesson.id)"
                                        aria-label="Ubah"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteButton
                                    :href="lessonRoutes.destroy.url(lesson.id)"
                                    title="Hapus mata pelajaran ini?"
                                    description="Ujian pada mata pelajaran ini juga akan terhapus."
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    :links="lessons.links"
                    :from="lessons.from"
                    :to="lessons.to"
                    :total="lessons.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
