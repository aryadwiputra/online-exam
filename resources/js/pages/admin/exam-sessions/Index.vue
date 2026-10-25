<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Pencil, Plus, Users } from '@lucide/vue';
import type { Paginated } from '@/types';
import { Badge } from '@/components/ui/badge';
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
import examSessions from '@/routes/admin/exam-sessions';
import enrollments from '@/routes/admin/exam-sessions/enrollments';

interface Session {
    id: number;
    title: string;
    start_time: string;
    end_time: string;
    enrollments_count: number;
    attempts_count: number;
    exam: { id: number; title: string } | null;
}

const props = defineProps<{
    sessions: Paginated<Session>;
    filters: { search: string };
}>();

const search = ref(props.filters.search);

watch(search, (value) => {
    window.setTimeout(() => {
        router.get(
            examSessions.index.url(),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="Sesi Ujian" />

    <PageHeader
        title="Sesi Ujian"
        description="Atur jadwal pelaksanaan dan peserta ujian."
    >
        <Button as-child>
            <Link :href="examSessions.create.url()">
                <Plus class="size-4" />
                Buat Sesi
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <Card>
        <CardContent class="space-y-4">
            <div class="max-w-sm">
                <Input v-model="search" placeholder="Cari sesi..." />
            </div>

            <EmptyState
                v-if="sessions.data.length === 0"
                title="Belum ada sesi ujian"
                description="Buat sesi untuk menentukan kapan ujian dapat dikerjakan."
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Sesi</TableHead>
                            <TableHead>Ujian</TableHead>
                            <TableHead>Jadwal</TableHead>
                            <TableHead>Peserta</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="session in sessions.data"
                            :key="session.id"
                        >
                            <TableCell class="font-medium">
                                {{ session.title }}
                            </TableCell>
                            <TableCell>{{ session.exam?.title }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">
                                {{ session.start_time }} —
                                {{ session.end_time }}
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-1">
                                    <Badge variant="secondary"
                                        >{{
                                            session.enrollments_count
                                        }}
                                        terdaftar</Badge
                                    >
                                    <Badge
                                        >{{
                                            session.attempts_count
                                        }}
                                        dikerjakan</Badge
                                    >
                                </div>
                            </TableCell>
                            <TableCell class="flex justify-end gap-1">
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="
                                            enrollments.index.url(session.id)
                                        "
                                        aria-label="Peserta"
                                    >
                                        <Users class="size-4" />
                                    </Link>
                                </Button>
                                <Button as-child variant="ghost" size="icon">
                                    <Link
                                        :href="
                                            examSessions.edit.url(session.id)
                                        "
                                        aria-label="Ubah"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteButton
                                    :href="examSessions.destroy.url(session.id)"
                                    title="Hapus sesi ini?"
                                    description="Peserta dan hasil pengerjaan pada sesi ini juga akan terhapus."
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    :links="sessions.links"
                    :from="sessions.from"
                    :to="sessions.to"
                    :total="sessions.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
