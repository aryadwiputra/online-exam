<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Pencil, Plus, Upload } from '@lucide/vue';
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
import classroomRoutes from '@/routes/admin/classrooms';

interface Classroom {
    id: number;
    title: string;
    students_count: number;
}

const props = defineProps<{
    classrooms: Paginated<Classroom>;
    filters: { search: string };
}>();

const search = ref(props.filters.search);

watch(search, (value) => {
    window.setTimeout(() => {
        router.get(
            classroomRoutes.index.url(),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="Kelas" />

    <PageHeader title="Kelas" description="Kelola daftar kelas peserta ujian.">
        <Button as-child>
            <Link :href="classroomRoutes.create.url()">
                <Plus class="size-4" />
                Tambah Kelas
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <Card>
        <CardContent class="space-y-4">
            <div class="max-w-sm">
                <Input v-model="search" placeholder="Cari kelas..." />
            </div>

            <EmptyState
                v-if="classrooms.data.length === 0"
                title="Belum ada kelas"
                description="Tambahkan kelas terlebih dahulu sebelum menambahkan siswa."
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama Kelas</TableHead>
                            <TableHead>Jumlah Siswa</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="classroom in classrooms.data"
                            :key="classroom.id"
                        >
                            <TableCell class="font-medium">
                                {{ classroom.title }}
                            </TableCell>
                            <TableCell>{{
                                classroom.students_count
                            }}</TableCell>
                            <TableCell class="flex justify-end gap-1">
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="
                                            classroomRoutes.edit.url(
                                                classroom.id,
                                            )
                                        "
                                        aria-label="Ubah"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteButton
                                    :href="
                                        classroomRoutes.destroy.url(
                                            classroom.id,
                                        )
                                    "
                                    title="Hapus kelas ini?"
                                    description="Siswa dan ujian pada kelas ini juga akan terhapus."
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    :links="classrooms.links"
                    :from="classrooms.from"
                    :to="classrooms.to"
                    :total="classrooms.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
