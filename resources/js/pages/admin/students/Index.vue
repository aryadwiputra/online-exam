<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Pencil, Plus, Upload } from '@lucide/vue';
import type { Paginated } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
import studentRoutes from '@/routes/admin/students';

interface Student {
    id: number;
    nisn: string;
    name: string;
    gender: 'L' | 'P';
    classroom: { id: number; title: string } | null;
}

interface Classroom {
    id: number;
    title: string;
}

const props = defineProps<{
    students: Paginated<Student>;
    classrooms: Classroom[];
    filters: { search: string; classroom: number | null };
}>();

const search = ref(props.filters.search);
const classroom = ref<string>(
    props.filters.classroom ? String(props.filters.classroom) : 'all',
);

function reload(): void {
    router.get(
        studentRoutes.index.url(),
        {
            search: search.value || undefined,
            classroom: classroom.value === 'all' ? undefined : classroom.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(reload, 300);
});
watch(classroom, reload);
</script>

<template>
    <Head title="Siswa" />

    <PageHeader title="Siswa" description="Kelola data peserta ujian.">
        <Button as-child variant="outline">
            <Link :href="studentRoutes.import.create.url()">
                <Upload class="size-4" />
                Impor CSV
            </Link>
        </Button>
        <Button as-child>
            <Link :href="studentRoutes.create.url()">
                <Plus class="size-4" />
                Tambah Siswa
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <Card>
        <CardContent class="space-y-4">
            <div class="flex flex-wrap gap-2">
                <Input
                    v-model="search"
                    placeholder="Cari nama atau NISN..."
                    class="max-w-xs"
                />
                <Select v-model="classroom">
                    <SelectTrigger class="w-48">
                        <SelectValue placeholder="Semua kelas" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua kelas</SelectItem>
                        <SelectItem
                            v-for="item in classrooms"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <EmptyState
                v-if="students.data.length === 0"
                title="Belum ada siswa"
                description="Tambahkan siswa satu per satu atau impor melalui file CSV."
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>NISN</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>Kelas</TableHead>
                            <TableHead>Gender</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="student in students.data"
                            :key="student.id"
                        >
                            <TableCell class="font-mono text-xs">
                                {{ student.nisn }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ student.name }}
                            </TableCell>
                            <TableCell>
                                {{ student.classroom?.title ?? '—' }}
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{
                                    student.gender === 'L'
                                        ? 'Laki-laki'
                                        : 'Perempuan'
                                }}</Badge>
                            </TableCell>
                            <TableCell class="flex justify-end gap-1">
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="
                                            studentRoutes.edit.url(student.id)
                                        "
                                        aria-label="Ubah"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteButton
                                    :href="
                                        studentRoutes.destroy.url(student.id)
                                    "
                                    title="Hapus siswa ini?"
                                    description="Riwayat pengerjaan ujian siswa tersebut juga akan terhapus."
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    :links="students.links"
                    :from="students.from"
                    :to="students.to"
                    :total="students.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
