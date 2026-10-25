<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Eye, Pencil, Plus } from '@lucide/vue';
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
import examRoutes from '@/routes/admin/exams';

interface Exam {
    id: number;
    title: string;
    duration: number;
    questions_count: number;
    sessions_count: number;
    lesson: { id: number; title: string } | null;
    classroom: { id: number; title: string } | null;
}

const props = defineProps<{
    exams: Paginated<Exam>;
    classrooms: { id: number; title: string }[];
    lessons: { id: number; title: string }[];
    filters: {
        search: string;
        classroom: number | null;
        lesson: number | null;
    };
}>();

const search = ref(props.filters.search);
const classroom = ref(
    props.filters.classroom ? String(props.filters.classroom) : 'all',
);
const lesson = ref(props.filters.lesson ? String(props.filters.lesson) : 'all');

function reload(): void {
    router.get(
        examRoutes.index.url(),
        {
            search: search.value || undefined,
            classroom: classroom.value === 'all' ? undefined : classroom.value,
            lesson: lesson.value === 'all' ? undefined : lesson.value,
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
watch([classroom, lesson], reload);
</script>

<template>
    <Head title="Ujian" />

    <PageHeader title="Ujian" description="Kelola ujian dan bank soalnya.">
        <Button as-child>
            <Link :href="examRoutes.create.url()">
                <Plus class="size-4" />
                Buat Ujian
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <Card>
        <CardContent class="space-y-4">
            <div class="flex flex-wrap gap-2">
                <Input
                    v-model="search"
                    placeholder="Cari judul ujian..."
                    class="max-w-xs"
                />
                <Select v-model="classroom">
                    <SelectTrigger class="w-44">
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
                <Select v-model="lesson">
                    <SelectTrigger class="w-52">
                        <SelectValue placeholder="Semua mata pelajaran" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all"
                            >Semua mata pelajaran</SelectItem
                        >
                        <SelectItem
                            v-for="item in lessons"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <EmptyState
                v-if="exams.data.length === 0"
                title="Belum ada ujian"
                description="Buat ujian, lalu tambahkan soal ke dalamnya."
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Judul</TableHead>
                            <TableHead>Kelas</TableHead>
                            <TableHead>Mapel</TableHead>
                            <TableHead>Durasi</TableHead>
                            <TableHead>Soal</TableHead>
                            <TableHead>Sesi</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="exam in exams.data" :key="exam.id">
                            <TableCell class="font-medium">
                                {{ exam.title }}
                            </TableCell>
                            <TableCell>{{ exam.classroom?.title }}</TableCell>
                            <TableCell>{{ exam.lesson?.title }}</TableCell>
                            <TableCell>{{ exam.duration }} menit</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        exam.questions_count
                                            ? 'secondary'
                                            : 'outline'
                                    "
                                    >{{ exam.questions_count }}</Badge
                                >
                            </TableCell>
                            <TableCell>{{ exam.sessions_count }}</TableCell>
                            <TableCell class="flex justify-end gap-1">
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="examRoutes.show.url(exam.id)"
                                        aria-label="Detail"
                                    >
                                        <Eye class="size-4" />
                                    </Link>
                                </Button>
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="examRoutes.edit.url(exam.id)"
                                        aria-label="Ubah"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteButton
                                    :href="examRoutes.destroy.url(exam.id)"
                                    title="Hapus ujian ini?"
                                    description="Soal dan sesi ujian ini juga akan terhapus."
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    :links="exams.links"
                    :from="exams.from"
                    :to="exams.to"
                    :total="exams.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
